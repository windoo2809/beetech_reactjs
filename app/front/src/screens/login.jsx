import React from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { useForm } from "react-hook-form";
import { useHistory } from "react-router-dom";
import LinkName from "../constants/link_name";
import validation from "../constants/validation";
import "../assets/scss/screens/login.scss";
import logo from "../assets/images/logo-login.svg";
import loginApi from "../api/loginApi";
import HeaderNew from "./layouts/header_new";
import FooterNew from "./layouts/footer_new";

function Login(props) {
  const [t] = useTranslation();
  const history = useHistory();
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    shouldUseNativeValidation: true,
  });

  const onSubmit = (e) => {
    loginApi
      .postLogin({
        email: e.loginID,
        password: e.password,
      })
      .then((data) => {
        history.push("/dashboard");
        console.log(data);
      })
      .catch((err) => {
        console.log(err);
      });
  };

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer not-login">
        <div className="page-template login-page">
          <Container>
            <div className="login-content">
              <div className="show-notification">
                <h2 className="title-login text-center mb-4 mb-lg-5">
                  お知らせ
                </h2>
                <div className="list">
                  <ul>
                    <li>
                      <a href={LinkName.DETAIL_NOTIFICATION}>
                        <span className="date mr-2">2022.09.28</span>
                        <span className="name">件名テスト</span>
                      </a>
                    </li>
                    <li>
                      <a href={LinkName.DETAIL_NOTIFICATION}>
                        <span className="date mr-2">2022.09.28</span>
                        <span className="name">件名テスト</span>
                      </a>
                    </li>
                    <li>
                      <a href={LinkName.DETAIL_NOTIFICATION}>
                        <span className="date mr-2">2022.09.28</span>
                        <span className="name">件名テスト</span>
                      </a>
                    </li>
                    <li>
                      <a href={LinkName.DETAIL_NOTIFICATION}>
                        <span className="date mr-2">2022.09.28</span>
                        <span className="name">件名テスト</span>
                      </a>
                    </li>
                    <li>
                      <a href={LinkName.DETAIL_NOTIFICATION}>
                        <span className="date mr-2">2022.09.28</span>
                        <span className="name">件名テスト</span>
                      </a>
                    </li>
                  </ul>
                </div>
                <div className="action-box">
                  <a href={LinkName.LIST_NOTIFICATION}>
                    {t("WEG_01_0100_text_link_see_list")}
                  </a>
                </div>
              </div>
              <div className="login-page__box">
                <h2 className="title-login text-center mb-4 mb-lg-3">
                  ログイン
                </h2>
                <div className="login-page__form">
                  <div className="logo-login text-center">
                    <img className="d-inline-block" src={logo} alt="" />
                  </div>
                  <div className="form-warp">
                    <form onSubmit={handleSubmit(onSubmit)}>
                      <div className="group-box">
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="text"
                                className="form-group__field form-control"
                                {...register("loginID", {
                                  required: t("VALID_014"),
                                  pattern: {
                                    value: validation.EMAIL.PATTERN,
                                    message: t("VALID_015"),
                                  },
                                })}
                                placeholder={t("WEG_01_0100_enterLoginID")}
                              />
                              {errors.loginID && (
                                <p className="text-danger font-weight-bold">
                                  {errors.loginID.message}
                                </p>
                              )}
                            </div>
                          </div>
                        </div>
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="text"
                                {...register("customer_login_id", {
                                  required: t("CUSTOMER_ID_LOGIN"),
                                })}
                                className="form-group__field form-control"
                                placeholder={t(
                                  "WEG_01_0100_placeholder_customer_login_id"
                                )}
                              />
                              {errors.customer_login_id && (
                                <p className="text-danger font-weight-bold">
                                  {errors.customer_login_id.message}
                                </p>
                              )}
                            </div>
                          </div>
                        </div>
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="password"
                                {...register("password", {
                                  required: t("VALID_018"),
                                })}
                                className="form-group__field form-control"
                                placeholder={t(
                                  "WEG_01_0100_enterLoginPassword"
                                )}
                              />
                              {errors.password && (
                                <p className="text-danger font-weight-bold">
                                  {errors.password.message}
                                </p>
                              )}
                            </div>
                          </div>
                        </div>
                        <div className="login-page__reset-pass">
                          <a href={LinkName.SEND_URL_RE_SETTING_PASSWORD}>
                            {t("WEG_01_0100_forgotPassword")}
                          </a>
                        </div>
                      </div>
                      <div className="action-box-btn">
                        <button className="btn btn-lg btn-primary w-100">
                          <strong>{t("WEG_01_0100_login")}</strong>
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </Container>
        </div>
      </div>
      <FooterNew />
    </>
  );
}

export default Login;
