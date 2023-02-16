import React from "react";
import logo from "../../assets/images/logo-black.svg";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import LinkName from "../../constants/link_name";
import "../../assets/scss/screens/layouts/header_ver_2.scss";

function HeaderNew() {
  const [t] = useTranslation();

  return (
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
                <a href={LinkName.MAIL_FORM}>{t("FORM_MAIL")}</a>
              </li>
              <li>
                <a href={LinkName.FAQ}>{t("FAQ")}</a>
              </li>
            </ul>
          </div>
        </div>
      </Container>
    </div>
  );
}
export default HeaderNew;
