import axios from 'axios'

type LaravelValidationErrors = Record<string, string[]>

export function getFieldErrors(error: unknown): LaravelValidationErrors {
  if (!axios.isAxiosError(error)) return {}

  const errors = (error.response?.data as { errors?: LaravelValidationErrors } | undefined)?.errors
  return errors ?? {}
}

export function getErrorMessage(error: unknown): string | null {
  if (!axios.isAxiosError(error)) return null

  return (error.response?.data as { message?: string } | undefined)?.message ?? null
}
