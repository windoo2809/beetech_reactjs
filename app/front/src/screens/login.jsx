import React, { useState } from "react";
import axios from "axios";
import { Container } from "react-bootstrap";
import { Trans } from "react-i18next";
import { useTranslation } from "react-i18next";
import LinkName from "../constants/link_name";
import "../assets/scss/screens/login.scss";
import "../assets/scss/screens/layouts/header_ver_2.scss";
import logo from "../assets/images/logo-black.svg";

function Login() {
  const [t] = useTranslation();
  const [loginID, setLoginID] = useState("");
  const [password, setPassword] = useState("");

  const handleLoginID = (e) => {
    setLoginID(e.target.value);
  };

  const handlePassword = (e) => {
    setPassword(e.target.value);
  };

  const handleApiLogin = (e) => {
    console.log({ loginID, password });
    e.preventDefault();
    axios.post("https://reqres.in/api/login", {
      email: loginID,
      password: password,
    })
      .then((res) => {
        console.log(res);
      })
      .catch((error) => {
        console.log(error);
      });
  };
  return (
    <>
      <header>
        <div className="site-header">
          <Container>
            <div className="site-header__box">
              <div className="logo">
                <a href="#">
                  <img src={logo} alt="logo" className="header-logo" />
                </a>
              </div>
              <div className="right-box">
                <ul className="nav-link">
                  <li>
                    <a href={LinkName.FORM_MAIL}>
                      <Trans i18nKey="FORM_MAIL" />
                    </a>
                  </li>
                  <li>
                    <a href={LinkName.FAQ}>
                      <Trans i18nKey="FAQ" />
                    </a>
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
                  <Trans i18nKey="WEG_01_0100_text_link_see_list" />
                </a>
              </div>
            </div>
            <div className="login-content">
              <div className="login-page__box">
                <div className="login-page__form">
                  <div className="text-login text-center">
                    <div className="d-inline-block">
                      <Trans i18nKey="WEG_01_0100_login" />
                    </div>
                  </div>
                  <div className="form-wrap">
                    <form>
                      <div className="group-box">
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="text"
                                value={loginID}
                                onChange={handleLoginID}
                                className="form-group__field form-control"
                                name="loginID"
                                placeholder={t('WEG_01_0100_enterLoginID')}
                              />
                            </div>
                          </div>
                        </div>
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="text"
                                className="form-group__field form-control"
                                name="customer_login_id"
                                placeholder={t('WEG_01_0100_placeholder_customer_login_id')}
                              />
                            </div>
                          </div>
                        </div>
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="password"
                                value={password}
                                onChange={handlePassword}
                                className="form-group__field form-control"
                                name="password"
                                placeholder={t('WEG_01_0100_enterLoginPassword')}
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                      <div className="action-box-btn">
                        <button
                          onClick={handleApiLogin}
                          className="btn btn-lg btn-primary w-100"
                        >
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
