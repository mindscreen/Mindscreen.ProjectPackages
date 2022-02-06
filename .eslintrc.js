module.exports = {
    'root': true,
    'parser': '@typescript-eslint/parser',
    'plugins': [
        '@typescript-eslint',
    ],
    'env': {
        'browser': true,
        'commonjs': true,
        'es6': true,
    },
    'extends': [
        'eslint:recommended',
        'plugin:@typescript-eslint/recommended',
    ],
    'parserOptions': {
        'sourceType': 'module',
    },
    'rules': {
        'indent': [
            'error',
            4,
            {
                'SwitchCase': 1,
            },
        ],
        'quotes': [
            'error',
            'single',
        ],
        'semi': [
            'error',
            'always',
        ],
        'comma-dangle': [
            'error',
            {
                'arrays': 'always-multiline',
                'objects': 'always-multiline',
                'imports': 'always-multiline',
                'exports': 'always-multiline',
                'functions': 'ignore',
            },
        ],
        'object-curly-spacing': [
            'warn',
            'always',
            {
                'objectsInObjects': true,
                'arraysInObjects': true,
            },
        ],
        'array-bracket-spacing': [
            'warn',
            'always',
        ],
        'no-console': [
            'warn',
            {
                'allow': [
                    'warn',
                    'error',
                ],
            },
        ],
        'space-infix-ops': [
            2,
        ],
        'no-trailing-spaces': [
            'error',
        ],
        'comma-spacing': [
            'error',
        ],
        'keyword-spacing': [
            'error',
        ],
        'arrow-spacing': [
            'error',
        ],
        'key-spacing': [
            'error',
        ],
        'no-multiple-empty-lines': [
            'warn',
            {
                'max': 2,
            },
        ],
        'no-empty': [
            'error',
            {
                'allowEmptyCatch': true,
            },
        ],
        'no-empty-function': [
            'error',
        ],
    },
};
