import React from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { useForm, FormProvider } from "react-hook-form";
import Common from "../../constants/common";
import LinkName from "../../constants/link_name";
import "../../assets/scss/screens/login.scss";
import logo from "../../assets/images/logo-login.svg";
import loginApi from "../../api/loginApi";
import HeaderNew from "../layouts/header_new";
import FooterNew from "../layouts/footer_new";
import FormLoginInput from "./components/form";

export default function Login(props) {
  const [t] = useTranslation();
  const methods = useForm({
    mode: "onChange",
    reValidateMode: "onChange",
    defaultValues: {},
  });

  const { handleSubmit } = methods;

  const handleSubmitForm = (e) => {
    loginApi
      .postLogin({
        email: e.loginID,
        password: e.password,
      })
      .then(
        (response) => {
          if (response.status === Common.HTTP_STATUS.OK) {
            props.history.push(LinkName.DASHBOARD);
          }
        },
        (error) => {
          console.log(error);
        }
      );
  };

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer not-login">
        <div className="page-template login-page">
          <Container>
            <div className="login-content">
              <div className="show-notification">
                <h2 className="title-login text-center mb-2 mb-lg-3">
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
                <h2 className="title-login text-center">ログイン</h2>
                <div className="login-page__form">
                  <div className="logo-login text-center">
                    <img className="d-inline-block" src={logo} alt="" />
                  </div>
                  <div className="form-warp">
                    <FormProvider {...methods}>
                      <form onSubmit={handleSubmit(handleSubmitForm)}>
                        <FormLoginInput />

                        <div className="action-box-btn">
                          <button className="btn btn-lg btn-primary w-100">
                            <strong>{t("WEG_01_0100_login")}</strong>
                          </button>
                        </div>
                      </form>
                    </FormProvider>
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
