// import scss
import "../../assets/scss/screens/layouts/footer.scss";

// import library
import React from "react";
import { Container } from "react-bootstrap";
import { Link, withRouter } from "react-router-dom";
import logo from "../../assets/images/login.svg";
import LinkName from "../../constants/link_name";

function Footer(props) {
  return (
    <>
      <footer className="site-footer">
        <Container>
          <div className="site-footer__nav">
            <ul>
              <li>
                <Link to={"#"} className="link">
                  会社概要
                </Link>
              </li>

              <li>
                <Link to={"#"} className="link">
                  利用規約
                </Link>
              </li>

              <li>
                <Link to={"#"} className="link">
                  プライバシーポリシー
                </Link>
              </li>

              <li>
                <Link to={LinkName.MAIL_FORM} className="link">
                  システムに関するお問合せ
                </Link>
              </li>

              <li>
                <Link to={"#"} className="link">
                  よくある質問
                </Link>
              </li>
            </ul>
          </div>

          <div className="site-footer__logo">
            <img src={logo} alt="logo" />
          </div>

          <div className="site-footer__copyright">
            &#169; 2017 Landmark, Ltd. All Rights Reserved.
          </div>
        </Container>
      </footer>
    </>
  );
}

export default withRouter(Footer);
