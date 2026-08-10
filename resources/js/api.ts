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

export const joinQuiz = async (roomName: string, studentId: string) => {
    try {
        const response = await apiClient.post('/api/quiz/join', {
            room_name: roomName,
            student_id: studentId
        });
        return response.data;
    } catch (error: any) {
        throw new Error(error.response?.data?.error || 'Failed to join room.');
    }
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
