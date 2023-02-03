import Validation from "../constants/validation";
import {isEmpty, isFunction} from "underscore";
import logoutApi from "../api/logoutApi";
import {Trans} from "react-i18next";
import React from "react";
import jwt_decode from "jwt-decode";
import Common from "../constants/common";
import Permission from "../constants/permission";
import moment from "moment";
import { getDaysInMonth } from "date-fns";

// helper functions here
export function fomatDateJP(dateString) {
    const daysOfWeek = {
        0: '日',
        1: '月',
        2: '火',
        3: '水',
        4: '木',
        5: '金',
        6: '土',
    };
    let dateFormat = '';

    if (dateString) {
        const newDateString = convertDate( dateString );

        const dateData = {
            'day': new Date(newDateString).getDay(),
            'date': new Date(newDateString).getDate(),
            'month': new Date(newDateString).getMonth() + 1,
            'year': new Date(newDateString).getFullYear(),
        };

        dateFormat = `${dateData.year}年${dateData.month}月${dateData.date}日（${daysOfWeek[dateData.day]}）`;
    }

    return dateFormat;
}

export function fomatDateFromToJP(dateFromString, dateToString) {
    if (!dateFromString && !dateFromString) {
        return '';
    }

    const daysOfWeek = {
        0: '日',
        1: '月',
        2: '火',
        3: '水',
        4: '木',
        5: '金',
        6: '土',
    };

    const aryReturn = [];
    if (dateFromString) {
        const newDateFromString = convertDate( dateFromString );

        const dateFromData = {
            'day': new Date(newDateFromString).getDay(),
            'date': new Date(newDateFromString).getDate(),
            'month': new Date(newDateFromString).getMonth() + 1,
            'year': new Date(newDateFromString).getFullYear(),
        };

        aryReturn.push(`${dateFromData.year}年${dateFromData.month}月${dateFromData.date}日（${daysOfWeek[dateFromData.day]}）`);
    }

    aryReturn.push(`　　～　　`);

    if (dateToString) {
        const newDateToString = convertDate( dateToString );

        const dateToData = {
            'day': new Date(newDateToString).getDay(),
            'date': new Date(newDateToString).getDate(),
            'month': new Date(newDateToString).getMonth() + 1,
            'year': new Date(newDateToString).getFullYear(),
        };

        aryReturn.push(`${dateToData.year}年${dateToData.month}月${dateToData.date}日（${daysOfWeek[dateToData.day]}）`);
    }
    return aryReturn.join('');
}

export function fomatDateStartEndJP(dateStartString, dateEndString) {
    if (!dateStartString && !dateEndString) {
        return '';
    }

    const aryReturn = [];
    if (dateStartString) {
        const newDateStartString = convertDate( dateStartString );

        const dateFromData = {
            'day': new Date(newDateStartString).getDay(),
            'date': new Date(newDateStartString).getDate(),
            'month': new Date(newDateStartString).getMonth() + 1,
            'year': new Date(newDateStartString).getFullYear(),
        };

        aryReturn.push(`${dateFromData.year}年${dateFromData.month}月${dateFromData.date}日`);
    }

    if (dateEndString) {
        aryReturn.push(` ～ `);
        const newDateEndString = convertDate( dateEndString );

        const dateToData = {
            'day': new Date(newDateEndString).getDay(),
            'date': new Date(newDateEndString).getDate(),
            'month': new Date(newDateEndString).getMonth() + 1,
            'year': new Date(newDateEndString).getFullYear(),
        };

        aryReturn.push(`${dateToData.year}年${dateToData.month}月${dateToData.date}日`);
    }
    return aryReturn.join('');
}

export function fomatSendMail(value = '') {
    switch (value) {
        case Common.INPUT_REQUEST.WANT_GUIDE_TYPE.MAIL:
            return <Trans i18nKey = "WEG_03_0101_email" />;
        case Common.INPUT_REQUEST.WANT_GUIDE_TYPE.BOTH:
            return <Trans i18nKey = "WEG_03_0101_both" />;
        case Common.INPUT_REQUEST.WANT_GUIDE_TYPE.NOT:
            return <Trans i18nKey = "WEG_03_0101_request_fax" />;
        default:
            return '';
    }
}

export function isValidEmail(email) {
    const regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    return regex.test(String(email).toLowerCase());
};

export function isEmptyObj(obj) {
    if (typeof obj === 'object' && obj !== null) {
        return Object.keys(obj).length === 0;
    }
    return true;
}

export function fomatDestination(value) {
    switch (value) {
        case '0':
            return '自社のみに送る';
        case '1':
            return '下請のみに送る';
        case '2':
            return '両方に送る';
        default:
            return '';
    }
}

export function fomatSMS(value) {
    switch (value) {
        case Common.INPUT_REQUEST.SUBCONTRACT_REMINDER_SMS_FLAG.YES:
            return 'あり';
        case Common.INPUT_REQUEST.SUBCONTRACT_REMINDER_SMS_FLAG.NO:
            return 'なし';
        default:
            return '';
    }
}

export function isValidDate(date) {
    const regex = new RegExp("^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$");
    return regex.test(String(date).toLowerCase());
}

export function convertDate(dateStr, format = 'YYYY-MM-DD') {
    let dateFormat = '';

    if (dateStr) {
        dateFormat = moment(dateStr).format( format );
    }

    return dateFormat;
}

export function convertDateTime(dateStr) {
    let dateFormat = '';

    if (dateStr) {
        dateFormat = moment(dateStr).format("YYYY-MM-DD HH:mm:ss");
    }

    return dateFormat;
}

export function isValidPassword(password) {
    const regex = new RegExp(Validation.PASSWORD.PATTERN);
    return regex.test(String(password));
}

export function replaceString(message, newValue = []) {

    const matchSubstrings = message.match(/{[0-9]+}/g);

    for (let i = 0; i < matchSubstrings.length; i++) {
        message = message.replace(matchSubstrings[i], newValue[i]);
    }

    return message;
}

export function customFomatDateTimeJP(dateString) {
    if ( !dateString ) {
        return '';
    }

    const newDateString = convertDate( dateString );

    const dateData = {
        'date': new Date(newDateString).getDate(),
        'month': new Date(newDateString).getMonth() + 1,
        'year': new Date(newDateString).getFullYear(),
        'hour': new Date(newDateString).getHours(),
        'minute': new Date(newDateString).getMinutes(),
    };

    return `${dateData.year}年${dateData.month}月${dateData.date}日 ${dateData.hour}時${dateData.minute}分`;
}

export function isValidNumber(number) {
    const regex = new RegExp('^0*[0-9]\\d*$');
    return regex.test(number);
}

export function fomatDate(dateString, formatDate = 'YYYY/MM/DD') {
    let dateFormat = '';

    if (dateString) {
        dateFormat = moment(dateString).format(formatDate);
    }

    return dateFormat;
}

export function conventPatternToRegex(pattern) {
    return new RegExp(pattern, "g");
}

export function clearLoginData() {
    //['userData', 'isLoggingIn', 'isFirstLogin', 'isManyBranch', 'isSuper'].forEach((key) => sessionStorage.removeItem(key));
    sessionStorage.clear();
}

export function caculatorDistance2Day(dateStart, dateEnd) {
    const date_start = new Date(dateStart);
    const date_end = new Date(dateEnd);
    const diffDays = parseInt((date_end - date_start) / (1000 * 60 * 60 * 24), 10);

    return diffDays;
}

// Get access token user login
export function getAccessTokenLogin() {
    let userData = {},
        accessToken = '';

    if ('userData' in sessionStorage && !isEmpty(JSON.parse(sessionStorage.getItem('userData')))) {
        userData = JSON.parse(sessionStorage.getItem('userData'));

        accessToken = `Bearer ${userData.access_token}`;
    }

    return accessToken;
}

export function queryString(param) {
    let search = window.location.search;
    let params = new URLSearchParams(search);
    return params.get(param);
}

// Get info user login
export function getInfoUserLogin() {
    let userData = {};

    if ('userData' in sessionStorage && !isEmpty(JSON.parse(sessionStorage.getItem('userData')))) {
        userData = JSON.parse(sessionStorage.getItem('userData'));
    }

    return userData;
}

/**
 * Get info user login from access token
 * 
 * @returns Object
 */
export function getUserFromAccessToken() {
    let userData = getInfoUserLogin();
    let accessToken = (userData.access_token) ? userData.access_token : '';

    let userInfo = {};
    if (accessToken) {
        const aryDataDecoded = jwt_decode(accessToken);
        userInfo = aryDataDecoded.user;
    }

    return userInfo;
}

export async function doLogout() {
    let result = false;
    try {
        await logoutApi.put();
        result = true;
    } catch (error) {
        console.error(error);
    }
    return result;
}

// check send sms
export function checkSendSms(value) {
    let text = '';

    if (value === 1) {
        text = < Trans i18nKey = "WEG_03_0105_yes" /> ;
    } else {
        text = < Trans i18nKey = "WEG_03_0105_no" /> ;
    }

    return text;
}

const __dataPrefectureMap = {
    1: '北海道',
    2: '青森県',
    3: '岩手県',
    4: '宮城県',
    5: '秋田県',
    6: '山形県',
    7: '福島県',
    8: '茨城県',
    9: '栃木県',
    10: '群馬県',
    11: '埼玉県',
    12: '千葉県',
    13: '東京都',
    14: '神奈川県',
    15: '新潟県',
    16: '富山県',
    17: '石川県',
    18: '福井県',
    19: '山梨県',
    20: '長野県',
    21: '岐阜県',
    22: '静岡県',
    23: '愛知県',
    24: '三重県',
    25: '滋賀県',
    26: '京都府',
    27: '大阪府',
    28: '兵庫県',
    29: '奈良県',
    30: '和歌山県',
    31: '鳥取県',
    32: '島根県',
    33: '岡山県',
    34: '広島県',
    35: '山口県',
    36: '徳島県',
    37: '香川県',
    38: '愛媛県',
    39: '高知県',
    40: '福岡県',
    41: '佐賀県',
    42: '長崎県',
    43: '熊本県',
    44: '大分県',
    45: '宮崎県',
    46: '鹿児島県',
    47: '沖縄県'
};

export function getPrefecture(prefCd) {
    if (prefCd) {
        prefCd = parseInt(prefCd, 10);
        return (__dataPrefectureMap[prefCd]) ? __dataPrefectureMap[prefCd] : '';
    }

    return '';
}

export function fomatRequestType(value) {
    switch (value) {
        case 0:
            return Common.REQUEST_TYPE_MAP[0];
        case 1:
            return Common.REQUEST_TYPE_MAP[1];
        case 2:
            return Common.REQUEST_TYPE_MAP[2];
        case 3:
            return Common.REQUEST_TYPE_MAP[3];
        case 4:
            return Common.REQUEST_TYPE_MAP[4];
        case 5:
            return Common.REQUEST_TYPE_MAP[5];
        case 6:
            return Common.REQUEST_TYPE_MAP[6];
        default:
            return '';

    }
}

export function checkExistHeaderCorrectFomat(headers){
    let headersCorrect = true;
    if(headers.length === Validation.LIST_HEADER.length){
        for(let i = 0; i < Validation.LIST_HEADER.length; i ++){
            if(headers[i] !== Validation.LIST_HEADER[i]){
                headersCorrect = false;
                break;
            }
        }
    }else{
        headersCorrect = false;
    }
    return headersCorrect;
}

export function csvFileToJSON(file) {
    return new Promise(function(resolve, reject) {
        const reader = new FileReader();

        reader.onerror = function(err) {
            reject(err);
        };

        reader.onload = function() {
            const sjisDecoder = new TextDecoder('shift-jis');
            const string_data = sjisDecoder.decode(reader.result);

            const dataRow = string_data.split('\n').filter(row => row !== "" && row !== "\r");
            const headers = dataRow[0].replace('\r','').split(',');
            const headersCorrect = checkExistHeaderCorrectFomat(headers);
            
            if(headersCorrect){
                if(dataRow.length > (Validation.MAX_RECORD_UPLOAD_USER + 1)){
                    resolve({data: "", errorValidate: true, message: "CMN0019-I"});
                }else{
                    resolve({data: CSVToArray(dataRow.join("\n")), errorValidate: false, message: ""});
                }
            }else{
                resolve({data: "", errorValidate: true, message: "CMN0018-I"});
            }
        };
        reader.readAsArrayBuffer(file);
    });
}

export function CSVToArray(strData, strDelimiter) {
    strDelimiter = (strDelimiter || ",");
    let objPattern = new RegExp(
        (
            "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

            "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

            "([^\"\\" + strDelimiter + "\\r\\n]*))"
        ),
        "gi");

    let arrData = [];
    let headers = [];
    let headersFound = false;
    let headerIndex = 0;

    let arrMatches = null;

    while (arrMatches = objPattern.exec(strData)) {
        let strMatchedDelimiter = arrMatches[1];
        if (strMatchedDelimiter.length && strMatchedDelimiter !== strDelimiter) {
            arrData.push({});
            headersFound = true;
            headerIndex = 0;
        }

        let strMatchedValue;
        if (arrMatches[2]) {
            strMatchedValue = arrMatches[2].replace(new RegExp("\"\"", "g"), "\"");
        } else {
            strMatchedValue = arrMatches[3];
        }

        if (!headersFound) {
            headers.push(strMatchedValue);
        } else {
            arrData[arrData.length - 1][headers[headerIndex]] = strMatchedValue;
            headerIndex++;
        }
    }
    return arrData;
}

/** Download file from content binary */
export function downloadFileFromContentBinary(data, fileName) {

    // It is necessary to create a new blob object with mime-type explicitly set
    // otherwise only Chrome works like it should
    const newBlob = new Blob([data], { type: data.type ?  data.type : 'application/json'});
    // IE doesn't allow using a blob object directly as link href
    // instead it is necessary to use msSaveOrOpenBlob
    if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        return window.navigator.msSaveOrOpenBlob(newBlob, fileName);
    }

    // Convert your blob into a Blob URL (a special url that points to an object in the browser's memory)
    const fileURL = window.URL.createObjectURL(newBlob);

    // Create a link element
    const link = document.createElement('a');
    link.href = fileURL;
    link.download = fileName;

    // Append link to the body
    document.body.appendChild(link);

    // Dispatch click event on the link
    // This is necessary as link.click() does not work on the latest firefox
    link.dispatchEvent(
        new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window
        })
    );

    // Remove link from body
    document.body.removeChild(link);
}

export function getPaymentStatus(paymentStatus) {
    switch (paymentStatus) {
        case 0:
            return '未払い';
        case 1:
            return '一部入金';
        case 2:
            return '支払完了';
        default:
            return '';
    }
}

export function getStylePaymentStatus(paymentStatus) {
    switch (paymentStatus) {
        case 0:
            return 'btn-danger';
        case 1:
            return 'btn-secondary';
        case 2:
            return 'btn-warning';
        default:
            return '';
    }
}

export function fomatDateTime(dateStr) {
    let dateFormat = '';

    if (dateStr) {
        const date = new Date(dateStr);
        const dateData = {
            year: date.getFullYear(),
            month: ("0" + (date.getMonth() + 1)).slice(-2),
            day: ("0" + date.getDate()).slice(-2),
            hours: ("0" + date.getHours()).slice(-2),
            minutes: ("0" + date.getMinutes()).slice(-2),
            seconds: ("0" + date.getSeconds()).slice(-2),
        }

        dateFormat = dateData.year + "-" + dateData.month + "-" + dateData.day + " " + dateData.hours + ":" + dateData.minutes + ":" + dateData.seconds;
    }

    return dateFormat;
}

export function formatDateTimeSecond(dateStr) {
    let dateFormat = '';

    if (dateStr) {
        dateFormat = moment(dateStr).format("YYYY/MM/DD HH:mm:ss");
    }

    return dateFormat;
}

export const asyncSessionStorage = {
    setItem: async function(key, value, callback = null) {
        sessionStorage.setItem(key, value);
        await null;
        if (isFunction(callback)) {
            callback();
        }
    },
    getItem: async function(key, callback = null) {
        sessionStorage.getItem(key);
        await null;
        if (isFunction(callback)) {
            callback();
        }
    }
};

export function formatDateAddText(dateString) {
    let dateFormat = '';

    if (dateString) {
        const newDateString = moment(dateString);

        const dateData = {
            'date': newDateString.format( 'DD' ),
            'month': newDateString.format( 'MM' ),
            'year': newDateString.format( 'YYYY' )
        };

        dateFormat = `${dateData.year}年${dateData.month}月${dateData.date}日`;
    }

    return dateFormat;
}

export function isValidFax(number) {
    const regex = new RegExp('[0-9]{10,11}');
    return regex.test(number);
}

export function resetAccessToken(accessToken, callback = null, lstItemRemove = null) {
    let storageData = JSON.parse(sessionStorage.getItem('userData'));
    let _userData = {
        ...storageData,
        access_token: accessToken
    };

    if (lstItemRemove && lstItemRemove.length > 0) {
        lstItemRemove.forEach((key) => sessionStorage.removeItem(key));
    }
    
    asyncSessionStorage.setItem('userData', JSON.stringify(_userData),  callback);
}

/**
 * check permission for page
 * 
 * @param {String} pathname 
 * @returns 
 */
export function checkPermissionForPage(pathname, userRole) {
    // user role
    if (!userRole) {
        const loginInfo = getUserFromAccessToken();
        userRole = loginInfo.role;
    }

    let pageInfo = Permission.NO_PERMISSION;
    if (pageInfo[pathname]) {
        let lstP = pageInfo[pathname];
        for (let index = 0; index < lstP.length; index++) {
            if (lstP[index] == userRole) {
                return false;
            }
        }
    }

    return true;
}

export function convertApplicationStatus(status){
    switch(status){
        case Common.APPLICATION_STATUS.APPLICATION:
            return '申請';
        case Common.APPLICATION_STATUS.APPROVAL:
            return '承認';
        case Common.APPLICATION_STATUS.REMAND:
            return '差戻';
        case Common.APPLICATION_STATUS.WITHDRAWAL:
            return '取下';
        default:
            return '';
    }
}

export function validURL(str) {
    const pattern = new RegExp("^https?://[\\w!?/+\\-_~;.,*&@#$%()'[\\]]+$");
    return pattern.test(str);
}

export function checkHeaderCsvUser( valCheck ) {
    const listHeader = [
        'login_id',
        'user_lock',
        'role',
        'customer_user_name',
        'customer_user_name_kana',
        'customer_user_tel',
        'customer_id',
        'customer_branch_id',
        'belong',
        'process_type'
    ];

    return valCheck.length === listHeader.length && valCheck.some( i => listHeader.includes(i) );
}

export function fomatSendDestinationType(send_destination_type) {
    let sendDestinationValue = '';
    switch (send_destination_type) {
        case Common.INPUT_REQUEST.SEND_DESTINATION_TYPE.COMPANY:
            sendDestinationValue = '自社へ送付';
            break;
        case Common.INPUT_REQUEST.SEND_DESTINATION_TYPE.SUBCONTRACT:
            sendDestinationValue = '下請へ送付';
            break;
        case Common.INPUT_REQUEST.SEND_DESTINATION_TYPE.BOTH:
            sendDestinationValue = '両方へ送付';
            break;
        default:
            sendDestinationValue = '';
    }
    return sendDestinationValue;
}

export function getDateValid(setDateValid){
    const dateNow = new Date();
    const date = {
        "year" : dateNow.getFullYear(),
        "month" : dateNow.getMonth() + 1,
        "day" : dateNow.getDate()
    }
    setDateValid(date);
}

export function convertNumberic(number){
    const fomatNumber = new Intl.NumberFormat();
    return fomatNumber.format(number);
}

export function addZero(i) {
    if (i < 10) {i = "0" + i}
    return i;
}

export function formatDateTimeAddText(dateString) {
    if ( !dateString ) {
        return '';
    }

    const newDateString = moment(dateString);

    const dateData = {
        'date': newDateString.format( 'DD' ),
        'month': newDateString.format( 'MM' ),
        'year': newDateString.format( 'YYYY' ),
        'hour': newDateString.format( 'HH' ),
        'minute': newDateString.format( 'mm' ),
    };

    return `${dateData.year}年${dateData.month}月${dateData.date}日 ${dateData.hour}:${dateData.minute}`;
}

export function numberWithCommas(number) {
    let result = 0;

    if ( number ) {
        result = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    return result;
}

export function checkCapacityFlag(flag) {
    let result = '×';

    if ( flag ) {
        result = '○';
    }

    return result;
}

export function handleEstAmount(
    startDate, 
    endDate,
    numbersCar, 
    Passability, 
    Prorated, 
    taxFlag, 
    parkingRent, 
    commission_discount_amt,
    key_money,
    fraction_amt_flag
    ) {
    // 1.1
    const newStartDateString = convertDate( startDate );
    const newEndDateString = convertDate( endDate );
    let totalMonth = 0;
    let totalPay = 0;
    let dayLeft = 0;
    const startDateData = {
        'date': new Date(newStartDateString).getDate(),
        'month': new Date(newStartDateString).getMonth() + 1,
        'year': new Date(newStartDateString).getFullYear(),
    };
    const endDateData = {
        'date': new Date(newEndDateString).getDate(),
        'month': new Date(newEndDateString).getMonth() + 1,
        'year': new Date(newEndDateString).getFullYear(),
    };
    totalMonth = (endDateData.year - startDateData.year) * 12;
    totalMonth -= startDateData.month;
    totalMonth += endDateData.month;
    const dayOfMonthDefault = Common.DAY_OF_MONTH;
    const dayOfMonthStart = new Date(startDateData.year, startDateData.month, 0).getDate();
    const dayOfMonthEnd = new Date(endDateData.year, endDateData.month, 0).getDate();
    const dayOfMonthAfterEnd = new Date(endDateData.year, endDateData.month - 1, 0).getDate();

    if (startDateData.date === 1 && endDateData.date === dayOfMonthEnd) {
        totalMonth += Common.PAY_UNIT.ONE_MONTH;
        totalPay += totalMonth * (+parkingRent);
    } else {
        if(startDateData.date > 1 && startDateData.month) {
            totalMonth -= Common.PAY_UNIT.ONE_MONTH;

            dayLeft += ((dayOfMonthStart - startDateData.date + 1));
        }
        if(endDateData.date === dayOfMonthEnd) {
            totalMonth += Common.PAY_UNIT.ONE_MONTH;
        } else {
            dayLeft += (endDateData.date);
        }
        totalPay = (totalMonth * (+parkingRent)) + (dayLeft * Math.trunc(+parkingRent / dayOfMonthDefault));
    }

    //1.2
    totalPay = (totalPay + (+commission_discount_amt) + (+key_money)) * (+numbersCar);
    //1.3
    if(!taxFlag) {
        totalPay += totalPay/10;
    }
    //1.4
    if(fraction_amt_flag) {
        totalPay = Math.ceil((totalPay.toFixed(0)/10)) * 10;
    }

    return totalPay > 0 ? Math.trunc(totalPay) : 0;
}
export function removeDuplicateObjectFromArray(array, key) {
    let check = {};
    let res = [];

    for( let i = 0; i < array.length; i++) {
        if(!check[array[i][key]]){
            check[array[i][key]] = true;
            res.push(array[i]);
        }
    }

    return res;
}

export function convertCurrencyToNumber(value) {
    let result = 0

    if (value) {
        result = value.replace(/,/g, '');
    }

    return result;
}

export function getInquiryType(inquiryType) {
    switch (inquiryType) {
        case Common.INQUIRY_TYPE.SERVICE:
            return 'お問合せ種別：サービスについて';
        case Common.INQUIRY_TYPE.USING:
            return 'お問合せ種別：利用方法について';
        case Common.INQUIRY_TYPE.OTHER_QUESTIONS:
            return 'お問合せ種別：その他のご質問';
        default:
            return '';
    }
}

export function getTextFormMail(data) {
    if (data) {
        return `お名前: ${data.name}\n会社名: ${data.company_name}\nメールアドレス: ${data.mail_address}\n本文: ${data.content}`;
    }
    return ""
}

//check last day of month
export function isLastDayOfMonth(date = new Date()) {
    //                 ms     sec  min  hour
    const oneDayInMs = 1000 * 60 * 60 * 24;
  
    return new Date(date.getTime() + oneDayInMs).getDate() === 1;
}

//check valid start date - case half month(date = 1 or 15)
export function validHalfMonthStartDate(date) {
    const scopeStartDateHalfMonth = [Common.PAY_UNIT_SCOPE_DAY.FIRST_DAY,Common.PAY_UNIT_SCOPE_DAY.MID_MONTH];
    return scopeStartDateHalfMonth.includes(date);
}

//check valid end date - case half month(date = 15 or last day)
export function validHalfMonthEndDate(date) {
    return (date.getDate() === Common.PAY_UNIT_SCOPE_DAY.MID_MONTH || isLastDayOfMonth(date));
}