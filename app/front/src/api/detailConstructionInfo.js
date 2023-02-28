import axiosClient from "./axiosClient";

const detailConstructionInfo = {
  getDetailConstructionInfo(params) {
    const url = "/users/2";
    return axiosClient.get(url, params);
  },
  postBranchId(params, config) {
    const url = "/login/branch";
    return axiosClient.post(url, params, config);
  },
};

export default detailConstructionInfo;
