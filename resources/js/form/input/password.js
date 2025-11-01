import { textfield } from './textfield';

export function password(options = {}) {

  const field = textfield({
    ...options,
    type: 'password'
  });

  return field;
}
