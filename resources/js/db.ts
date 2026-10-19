import Dexie, { type Table } from 'dexie';

// ─── Shared Types ────────────────────────────────────────────────────────────

export interface QuestionData {
    questionId: number;
    text: string;
    image: string | null;
    option1: string;
    option2: string;
    option3: string;
    option4: string;
    duration: number;
}

export interface AnswerData {
    questionId: number;
    selectedOption: number;
    answeredAt: string;
}

// ─── Table 1: ActiveQuiz ─────────────────────────────────────────────────────
// One document per in-flight exam. Everything needed to run and recover the
// quiz is stored in a single denormalized document, keyed by sessionId.

export interface ActiveQuiz {
    sessionId: string;       // Primary Key (UUIDv4, generated on client at start)
    studentId: string;
    quizId: number;
    attemptId: number;
    attemptToken: string;    // 64-char server-issued secret for submission auth
    quiz: {
        title: string;
        duration: number;
        start_datetime: string;
        end_datetime?: string | null;
    };
    questions: QuestionData[];


    // Answers stored as a flat map: { [questionId]: AnswerData }
    answers: Record<number, AnswerData>;

    progress: {
        currentQuestionIndex: number;
        remainingTime: number;

        questionEndTime: number;  // epoch ms — wall-clock deadline for current Q
        lastSaved: string;        // ISO string — used for abandoned session reaping
    };
}

// ─── Table 2: PendingSubmission ───────────────────────────────────────────────
// Offline outbox. Holds final answer payload until Wi-Fi is restored.

export interface PendingSubmission {
    id?: number;             // Auto-incremented PK
    submissionId: string;    // UUIDv4 idempotency key — generated once, replayed on retry
    sessionId: string;
    studentId: string;
    quizId: number;
    attemptId: number;
    attemptToken: string;
    answers: AnswerData[];
    createdAt: string;
    lastAttemptAt?: string;
    attemptCount: number;
    synced: number;          // 0 = pending, 1 = done (filter hook)
}

// ─── Utility Functions ────────────────────────────────────────────────────────

export const createSessionId = (): string => {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    return `${Date.now()}-${Math.random().toString(36).slice(2)}-${Math.random().toString(36).slice(2)}`;
};

// Store the active sessionId in sessionStorage so it survives a page reload.
// Each browser tab has its own sessionStorage, giving automatic tab isolation.
const SESSION_KEY = 'quiz-active-session-id';

export const saveActiveSessionId = (sessionId: string): void => {
    if (typeof sessionStorage !== 'undefined') {
        sessionStorage.setItem(SESSION_KEY, sessionId);
    }
};

export const loadActiveSessionId = (): string | null => {
    if (typeof sessionStorage === 'undefined') return null;
    return sessionStorage.getItem(SESSION_KEY);
};

export const clearActiveSessionId = (): void => {
    if (typeof sessionStorage !== 'undefined') {
        sessionStorage.removeItem(SESSION_KEY);
    }
};

// ─── Database ─────────────────────────────────────────────────────────────────

export class QuizDatabase extends Dexie {
    activeQuizzes!: Table<ActiveQuiz, string>;
    pendingSubmissions!: Table<PendingSubmission, number>;

    constructor() {
        super('QuizAppDB');

        this.version(1).stores({
            activeQuizzes: 'sessionId, quizId',
            pendingSubmissions: '++id, synced, sessionId, submissionId',
        });
    }
}

export const db = new QuizDatabase();

// ─── Helper Functions ─────────────────────────────────────────────────────────

const ABANDONED_SESSION_GRACE_MS = 72 * 60 * 60 * 1000; // 72 hours

/**
 * Delete the active quiz document for a given sessionId.
 * Optionally also remove its entry from the pending submissions outbox.
 */
export async function clearActiveQuiz(sessionId: string, removePending = false): Promise<void> {
    if (!sessionId || typeof sessionId !== 'string') return;

    clearActiveSessionId();

    const deletes: Promise<any>[] = [
        db.activeQuizzes.delete(sessionId),
    ];
    if (removePending) {
        deletes.push(db.pendingSubmissions.where('sessionId').equals(sessionId).delete());
    }
    await Promise.all(deletes);
}

/**
 * On app startup, wipe any active quiz documents older than 72 hours that
 * are not backed by a pending submission (i.e., genuinely abandoned sessions).
 */
export async function cleanupAbandonedSessions(now = Date.now()): Promise<number> {
    const active = await db.activeQuizzes.toArray();
    const pending = await db.pendingSubmissions.where('synced').equals(0).toArray();
    const pendingSessionIds = new Set(pending.map(s => s.sessionId));
    let removed = 0;

    for (const quiz of active) {
        if (pendingSessionIds.has(quiz.sessionId)) continue;
        const lastActivity = new Date(quiz.progress.lastSaved).getTime();
        if (!Number.isFinite(lastActivity) || now - lastActivity < ABANDONED_SESSION_GRACE_MS) continue;
        await db.activeQuizzes.delete(quiz.sessionId);
        removed += 1;
    }

    return removed;
}
