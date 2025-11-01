import { apiFetch } from './api-fetch';

export function updateUserConfig(data) {
  apiFetch({
    url: '/admin/api/update-user-config',
    data: {
      data,
    },
  });
}
