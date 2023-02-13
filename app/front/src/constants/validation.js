export default {
    INPUT_MAX_LENGTH: 255,
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
    }
};