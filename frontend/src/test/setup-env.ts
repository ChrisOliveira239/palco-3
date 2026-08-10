import { TextDecoder, TextEncoder } from 'node:util'

process.env.VITE_API_URL ??= 'http://localhost:3000'

// jsdom não expõe esses globals do Node por padrão; react-router precisa deles.
global.TextEncoder ??= TextEncoder
// @ts-expect-error TextDecoder do Node não bate 100% com o tipo do lib.dom, inofensivo em teste.
global.TextDecoder ??= TextDecoder
