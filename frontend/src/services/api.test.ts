import { AxiosHeaders } from 'axios'
import { attachAuthToken } from './api'

describe('attachAuthToken', () => {
  afterEach(() => {
    localStorage.clear()
  })

  it('anexa Bearer token quando existe no localStorage', () => {
    localStorage.setItem('token', 'abc123')

    const config = attachAuthToken({ headers: new AxiosHeaders() } as never)

    expect(config.headers.get('Authorization')).toBe('Bearer abc123')
  })

  it('não anexa header quando não há token', () => {
    const config = attachAuthToken({ headers: new AxiosHeaders() } as never)

    expect(config.headers.get('Authorization')).toBeUndefined()
  })
})
