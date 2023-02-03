import axiosClient from './axiosClient';
import { getAccessTokenLogin } from "../helpers/helpers";

const logoutApi = {
    put() {
        const url = '/logout';
        return axiosClient.put(url, {}, {
            headers: {
                Authorization: getAccessTokenLogin()
            }
        });
    },
};

export default logoutApi;