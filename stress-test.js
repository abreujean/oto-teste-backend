import http from 'k6/http';
import { sleep, check } from 'k6';

// URL base da sua API
const BASE_URL = 'http://127.0.0.1:8000/api';

// Token de autenticação, estou usando jwt
const AUTH_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvYXBpL2xvZ2luIiwiaWF0IjoxNzU3ODAzMzM3LCJleHAiOjE3NTc4MDY5MzcsIm5iZiI6MTc1NzgwMzMzNywianRpIjoic1hSaTZPdEN0WncwVmNtcCIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.fAaaIE1PcYSYm3vBeCgl1L8zLYSCDZQGc4-aCVKjdg8';

const params = {
  headers: {
    'Authorization': `Bearer ${AUTH_TOKEN}`,
    'Content-Type': 'application/json',
  },
};

// Cenário de teste
export const options = {
  stages: [
    // Aumenta gradualmente a carga
    { duration: '2m', target: 200 }, // Sobe para 200 usuários virtuais em 2 minutos
    { duration: '5m', target: 200 }, // Mantém 200 usuários por 5 minutos
    { duration: '2m', target: 0 },   // Desce para 0 usuários em 2 minutos
  ],
  thresholds: {
    // Define critérios de sucesso: 95% das requisições devem ser abaixo de 500ms
    http_req_duration: ['p(95)<500'],
    // Menos de 1% de erros
    http_req_failed: ['rate<0.01'],
  },
};

export default function () {
  // Simula uma requisição para listar produtos
  const res = http.get(`${BASE_URL}/products`, params);

  // Verifica se a requisição foi bem-sucedida (código 200)
  check(res, { 'status was 200': (r) => r.status == 200 });

  // Pausa por 1 segundo entre as requisições de um mesmo usuário virtual
  sleep(1);
}