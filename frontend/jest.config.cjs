/** @type {import('jest').Config} */
module.exports = {
  testEnvironment: 'jsdom',
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/src/$1',
  },
  setupFiles: ['<rootDir>/src/test/setup-env.ts'],
  setupFilesAfterEnv: ['<rootDir>/src/test/setup-tests.ts'],
}
