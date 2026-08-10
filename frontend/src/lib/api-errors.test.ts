import { getErrorMessage, getFieldErrors } from './api-errors'

function axiosError(data: unknown) {
  return {
    isAxiosError: true,
    response: { data },
  }
}

describe('getFieldErrors', () => {
  it('extrai erros de validação por campo de um AxiosError 422', () => {
    const error = axiosError({
      message: 'Dados inválidos.',
      errors: { email: ['O campo email é obrigatório.'] },
    })

    expect(getFieldErrors(error)).toEqual({ email: ['O campo email é obrigatório.'] })
  })

  it('retorna objeto vazio quando não há errors no payload', () => {
    const error = axiosError({ message: 'Erro genérico.' })

    expect(getFieldErrors(error)).toEqual({})
  })

  it('retorna objeto vazio quando não é um AxiosError', () => {
    expect(getFieldErrors(new Error('erro qualquer'))).toEqual({})
  })
})

describe('getErrorMessage', () => {
  it('extrai a mensagem do payload de um AxiosError', () => {
    const error = axiosError({ message: 'As credenciais informadas não conferem.' })

    expect(getErrorMessage(error)).toBe('As credenciais informadas não conferem.')
  })

  it('retorna null quando não é um AxiosError', () => {
    expect(getErrorMessage(new Error('erro qualquer'))).toBeNull()
  })
})
