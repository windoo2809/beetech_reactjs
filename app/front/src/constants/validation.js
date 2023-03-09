export default {
  INPUT_MAX_LENGTH: 255,
  INPUT_EMAIL_MAX_LENGTH: 2048,
  TEXTAREA_MAX_LENGTH: 200000,
  TEXT: {
    MAX_LENGTH: 1,
  },
  NUMBER: {
    PATTERN: "^[0-9]{10,11}$",
  },
  DATE: {
    FORMAT: "yyyy-MM-dd",
    PATTERN: /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/
  },
  EMAIL: {
    PATTERN:
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
  },
  PASSWORD: {
    PATTERN: /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,32}$/,
  },
  ZIP_CODE: {
    PATTERN: /^([0-9]{3})-([0-9]{4})$/,
  },
  PARKING_LOT: {
    WAGON_CAR: {
      MAX: 1000,
      MIN: 0,
    },
    AMOUNT_LIGHT_TRUCK: {
      MAX: 1000,
      MIN: 0,
    },
    TWO_T_TRUCK: {
      MAX: 1000,
      MIN: 0,
    },
    AMOUNT_DIFF: {
      MAX: 1000,
      MIN: 0,
    },
    OTHER_DETAILS: {
      MAX_LENGTH: 20000,
    },
    HOPE: {
      MAX_LENGTH: 20000,
    },
    MAIL_CC: {
      MAX_LENGTH: 2048,
      PATTERN:
        /^[\W]*([\w+\-.%]+@[\w\-.]+\.[A-Za-z]{2,4}[\W]*,{1}[\W]*)*([\w+\-.%]+@[\w\-.]+\.[A-Za-z]{2,4})[\W]*$/,
    },
  },
};
