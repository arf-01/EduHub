// resources/js/api.ts
import axios from 'axios';

// Create a configured axios instance
const apiClient = axios.create({
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Since this is Laravel, we also need to include the CSRF token if not using Sanctum token based auth.
// Wait, the API routes /api/quiz/join are stateless, but if we have CORS or CSRF issues, we might need it.
// Assuming it's a standard API endpoint.

export interface RoomQuizItem {
    id: number;
    title: string;
    status: 'idle' | 'scheduled' | 'live' | 'ended' | 'submitted';
    question_count: number;
    duration: number; // in seconds
    start_datetime: string | null;
    end_datetime: string | null;
    server_time: string;
    score: number | null;
    total: number | null;
}

export interface RoomQuizzesResponse {
    teacher_name: string;
    room_name: string;
    student_id: string;
    server_time: string;
    quizzes: RoomQuizItem[];
}

export const getRoomQuizzes = async (roomName: string, studentId: string): Promise<RoomQuizzesResponse> => {
    try {
        const response = await apiClient.post('/api/quiz/room-quizzes', {
            room_name: roomName,
            student_id: studentId
        });
        return response.data;
    } catch (error: any) {
        throw new Error(error.response?.data?.error || 'Failed to connect to room.');
    }
};

export const startQuiz = async (quizId: number, studentId: string) => {
    try {
        const response = await apiClient.post('/api/quiz/start', {
            quiz_id: quizId,
            student_id: studentId
        });
        return response.data;
    } catch (error: any) {
        throw new Error(error.response?.data?.error || 'Failed to start quiz.');
    }
};

export const joinQuiz = async (roomName: string, studentId: string) => {
    return getRoomQuizzes(roomName, studentId);
};

export const submitQuiz = async (quizId: number, studentId: string, answers: any[]) => {
    try {
        const response = await apiClient.post('/api/quiz/submit', {
            quiz_id: quizId,
            student_id: studentId,
            answers: answers
        });
        return response.data;
    } catch (error: any) {
        throw new Error(error.response?.data?.error || 'Failed to submit quiz.');
    }
};
