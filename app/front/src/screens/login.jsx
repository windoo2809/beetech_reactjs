import React from "react";
import "../assets/scss/screens/login.scss";
import "../assets/scss/screens/layouts/header_ver_2.scss";
import { Container } from "react-bootstrap";
import logo from "../assets/images/logo-black.svg";

function Login() {
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
                    <a href="#">
                      <span>システムに関するお問い合わせリンク</span>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <span>よくある質問リンク</span>
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
                    <a href="#">
                      <span>件名テスト</span>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <span>件名テスト</span>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <span>件名テスト</span>
                    </a>
                  </li>
                </ul>
              </div>
              <div className="action-box">
                <a href="#">
                  <span>一覧を見る</span>
                </a>
              </div>
            </div>
            <div className="login-content">
              <div className="login-page__box">
                <div className="login-page__form">
                  <div className="text-login text-center">
                    <div className="d-inline-block">
                      <span>ログイン</span>
                    </div>
                  </div>
                  <div className="form-wrap">
                    <form>
                      <div className="group-box">
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="email"
                                className="form-group__field form-control"
                                name="loginID"
                                placeholder="ログインIDを入力してください。"
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
                                placeholder="会社IDを入力してください。"
                              />
                            </div>
                          </div>
                        </div>
                        <div className="form-group">
                          <div className="form-group__control">
                            <div className="reset-box">
                              <input
                                type="password"
                                className="form-group__field form-control"
                                name="password"
                                placeholder="パスワードを入力してください。"
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                      <div className="action-box-btn">
                        <button className="btn btn-lg btn-primary w-100">
                          <strong>ログイン</strong>
                        </button>
                      </div>
                      <div className="login-page__reset-pass">
                        <a href="/send-url-re-setting-password">
                          <span>パスワードをお忘れの方はこちら</span>
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
