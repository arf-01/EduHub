import http from 'k6/http';
import { check } from 'k6';

export const options = {
    scenarios: {
        students_joining: {
            executor: 'per-vu-iterations',
            vus: 500,              // CHANGE THIS: 120, 200, 300, 500
            iterations: 1,
            maxDuration: '30s',
        },
    },
};

export default function () {

    const res = http.post(
        'http://localhost/api/quiz/join',
        JSON.stringify({
            room_name: 'arafath',
            student_id: `student-${__VU}-${__ITER}-${Date.now()}`,
        }),
        {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }
    );

    check(res, {
        'status is 200': (r) => r.status === 200,
    });
}

