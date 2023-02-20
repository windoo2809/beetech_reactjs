import axiosClient from "./axiosClient";

const customer = {
  getCustomer(params) {
    const url = "/users/1";
    return axiosClient.get(url, params);
  },
  postBranchId(params, config) {
    const url = "/login/branch";
    return axiosClient.post(url, params, config);
  },
};

export default customer;
