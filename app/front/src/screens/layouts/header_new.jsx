import React from "react";
import { Link } from "react-router-dom";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import LinkName from "../../constants/link_name";
import "../../assets/scss/screens/layouts/header_ver_2.scss";
import logo from "../../assets/images/login.svg";
import ImageIconHelp from "../../assets/images/icon/icon-help.png";
import ImageIconFAQ from "../../assets/images/icon/icon-faq.png";
import ImageIconBell from "../../assets/images/icon/icon-bell.png";

function HeaderNew() {
  const [t] = useTranslation();

  return (
    <header className="site-header">
      <Container>
        <div className="site-header__box">
          <div className="logo">
            <Link to={LinkName.LOGIN}>
              <img src={logo} className="header-logo" alt="logo" />
            </Link>
          </div>
          <div className="search-box d-none d-lg-flex align-items-lg-center"></div>
          <div className="right-box">
            <div className="login-box">
              <Link to={LinkName.LOGIN}>
                <span>{t("WEG_01_0100_login")}</span>
              </Link>
            </div>
            <ul className="nav-link">
              <li>
                <Link to={LinkName.MAIL_FORM}>
                  <img src={ImageIconHelp} alt="help" />
                </Link>
              </li>
              <li>
                <Link to={LinkName.FAQ}>
                  <img src={ImageIconFAQ} alt="faq" />
                </Link>
              </li>
              <li className="notification d-none d-lg-block">
                <Link to={LinkName.BELL}>
                  <img src={ImageIconBell} alt="bell" />
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </Container>
    </header>
  );
}
export default HeaderNew;
