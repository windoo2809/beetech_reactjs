import React, { useState } from "react";
import axios from "axios";
import { Container } from "react-bootstrap";
import { Trans } from "react-i18next";
import { useTranslation } from "react-i18next";
import { useForm } from "react-hook-form";
import LinkName from "../constants/link_name";
import validation from "../constants/validation";
import "../assets/scss/screens/login.scss";
import "../assets/scss/screens/layouts/header_ver_2.scss";
import logo from "../assets/images/logo-black.svg";
import loginApi from "../api/loginApi";

function Login(props) {
  const [t] = useTranslation();
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    shouldUseNativeValidation: true,
  });

  const onSubmit = async e => {
    try {
      await loginApi
        .postLogin({
          email: e.loginID,
          password: e.password,
        })
        .then((data) => {
          console.log(data);
        });
    } catch (data) {
      console.error(data);
    }
  };

  return (
    <>
      <header>
        <div className="site-header">
          <Container>
            <div className="site-header__box">
              <div className="logo">
                <a href={LinkName.LOGIN}>
                  <img src={logo} alt="logo" className="header-logo" />
                </a>
              </div>
              <div className="right-box">
                <ul className="nav-link">
                  <li>
                    <a href={LinkName.FORM_MAIL}>{t("FORM_MAIL")}</a>
                  </li>
                  <li>
                    <a href={LinkName.FAQ}>{t("FAQ")}</a>
                  </li>
                </ul>
              </div>
            </div>
          </Container>
        </div>
      </header>
      <div className="sticky-footer not-login">
        <div className="page-template login-page">
          <Container>
            <div className="show-notification">
              <div className="list">
                <ul>
                  <li>
                    <a href={LinkName.DETAIL_NOTIFICATION}>
                      <span>件名テスト</span>
                    </a>
                  </li>
                  <li>
                    <a href={LinkName.DETAIL_NOTIFICATION}>
                      <span>件名テスト</span>
                    </a>
                  </li>
                  <li>
                    <a href={LinkName.DETAIL_NOTIFICATION}>
                      <span>件名テスト</span>
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
            <div className="login-content">
              <div className="login-page__box">
                <div className="login-page__form">
                  <div className="text-login text-center">
                    <div className="d-inline-block">
                      {t("WEG_01_0100_login")}
                    </div>
                  </div>
                  <div className="form-wrap">
                    <form onSubmit={handleSubmit(onSubmit)}>
                      <div className="group-box">
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="text"
                                {...register("loginID", {
                                  required: t("VALID_014"),
                                  pattern: {
                                    value: validation.EMAIL.PATTERN,
                                    message: t("VALID_015"),
                                  },
                                })}
                                className="form-group__field form-control"
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
                                  // pattern: {
                                  //   value: validation.PASSWORD.PATTERN,
                                  //   message: t("VALID_019"),
                                  // },
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
                      </div>
                      <div className="action-box-btn">
                        <button className="btn btn-lg btn-primary w-100">
                          <strong>
                            <Trans i18nKey="WEG_01_0100_login" />
                          </strong>
                        </button>
                      </div>
                      <div className="login-page__reset-pass">
                        <a href={LinkName.SEND_URL_RE_SETTING_PASSWORD}>
                          <Trans i18nKey="WEG_01_0100_forgotPassword" />
                        </a>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </Container>
        </div>
      </div>
    </>
  );
}

export default Login;
