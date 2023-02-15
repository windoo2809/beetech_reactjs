import axiosClient from "./axiosClient";

const formMail = {
  postMail(params) {
    const url = "/register";
    return axiosClient.post(url, params);
  },
  postBranchId(params, config) {
    const url = "/login/branch";
    return axiosClient.post(url, params, config);
  },
};

export default formMail;
