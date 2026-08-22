import Dexie, { type Table } from 'dexie';

// Define the interfaces based on our schema
export interface Quiz {
    id: number;
    title: string;
    duration: number;
    start_datetime: string;
}

export interface Question {
    id: number;
    quizId: number;
    text: string;
    image: string | null;
    imageData: string | null; // Base64 Data URL for instant offline rendering
    option1: string;
    option2: string;
    option3: string;
    option4: string;
    duration: number;
    // NOTE: right_option is explicitly omitted for security
}

export interface Answer {
    questionId: number;
    selectedOption: number; // 1, 2, 3, or 4
    answeredAt: string; // ISO timestamp
}

export interface QuizState {
    id?: number; // Auto-incrementing primary key or fixed ID like 1
    studentId: string;
    quizId: number;
    currentQuestionId: number;
    remainingTime: number;
    questionEndTime?: number; // Absolute epoch timestamp (ms) for exact wall-clock deadline
    lastSaved: string; // ISO timestamp
}

export interface PendingSubmission {
    id?: number;
    studentId: string;
    quizId: number;
    answers: Answer[]; // Array of the student's selected options
    createdAt: string; // ISO timestamp
    synced: number; // 0 for false, 1 for true to avoid IDBKeyRange boolean errors
}

// Database class
export class QuizDatabase extends Dexie {
    quizzes!: Table<Quiz, number>;
    questions!: Table<Question, number>;
    answers!: Table<Answer, number>;
    quizState!: Table<QuizState, number>;
    pendingSubmissions!: Table<PendingSubmission, number>;

    constructor() {
        super('QuizAppDB');
        // Define the schema
        this.version(2).stores({
            quizzes: 'id', // Primary key is id
            questions: 'id, quizId', // Primary key is id, index on quizId
            answers: 'questionId', // Primary key is questionId (since a student can only have 1 answer per question)
            quizState: '++id, quizId', // Auto-increment id, index on quizId
            pendingSubmissions: '++id, synced' // Auto-increment id, index on synced status
        });
    }
}

// Export a singleton instance
export const db = new QuizDatabase();
