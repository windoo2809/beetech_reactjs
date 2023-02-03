import axiosClient from './axiosClient';

const loginApi = {
  postLogin(params) {
    const url = '/login';
    return axiosClient.post(url, params);
  },
  postBranchId(params, config) {
    const url = '/login/branch';
    return axiosClient.post(url, params, config);
  },
};

export default loginApi;