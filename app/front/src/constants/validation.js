export default {
    INPUT_MAX_LENGTH: 255,
    INPUT_EMAIL_MAX_LENGTH: 2048,
    TEXTAREA_MAX_LENGTH: 200000,
    TEXT: {
        MAX_LENGTH: 1,
    },
    NUMBER: {
        PATTERN: '^[0-9]{10,11}$'
    },
    DATE: {
        FORMAT: 'yyyy-MM-dd',
    },
    EMAIL: {
        PATTERN: /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
    },
    PASSWORD: {
        PATTERN: /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,32}$/,
    },
    ZIP_CODE: {
        PATTERN: /^([0-9]{3})-([0-9]{4})$/
    }

};