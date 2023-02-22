import {
  faChevronLeft,
  faChevronRight,
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { useEffect, useState } from "react";
import { Trans } from "react-i18next";
import { Link, withRouter } from "react-router-dom";
import iconRequestQuote from "../../assets/images/icon/icon-request-quote.png";
import "../../assets/scss/screens/layouts/main_sidebar.scss";
import Common from "../../constants/common";
import LinkName from "../../constants/link_name";
import { getUserFromAccessToken } from "../../helpers/helpers";

function MainSidebar(props) {
  // get info user login
  const loginUserInfo = getUserFromAccessToken();

  const userRoleListOrder = [
    Common.USER.ROLE.SUPER_USER,
    Common.USER.ROLE.SYSTEM_ADMIN,
    Common.USER.ROLE.APPROVER_USER,
    Common.USER.ROLE.GENERAL_USER,
  ];

  // check show link order
  const [isShowLinkRequestOrder, setShowLinkRequestOrder] = useState(false);
  useEffect(() => {
    let isMounted = true;

    if (isMounted && loginUserInfo) {
      if (userRoleListOrder.includes(loginUserInfo.role)) {
        setShowLinkRequestOrder(true);
      }
    }

    return () => {
      isMounted = false;
    };
  }, [loginUserInfo, userRoleListOrder]);

  // select modal request type
  const toScreenConstructionInfo = () => {
    sessionStorage.setItem(
      "parametersURL",
      JSON.stringify({
        project_id: "",
        request_id: "",
        progress_status: "",
      })
    );
    sessionStorage.removeItem("dataInputRequest");
    sessionStorage.removeItem("dataConstructionInfo");
    sessionStorage.removeItem("checkRequired");
    sessionStorage.removeItem("isSaveData");
    sessionStorage.removeItem("dataIsConstructionNumberRequired");
    sessionStorage.removeItem("saveDataConstructionInfo");
    sessionStorage.removeItem("originDataConstructionInfo");
    sessionStorage.removeItem("disabledWantGuideType");
    if (window.location.pathname === LinkName.CONSTRUCTION_INFO) {
      window.location.reload();
    }
    props.history.push(LinkName.CONSTRUCTION_INFO);
  };

  // event navbar
  const [isClickNavbarSidebar, setClickNavbarSidebar] = useState(false);
  useEffect(() => {
    let isMounted = true;

    if (isMounted && !isClickNavbarSidebar) {
      document.body.classList.remove("sidebar-collapse");
    }

    return () => {
      isMounted = false;
    };
  }, [isClickNavbarSidebar]);

  const handleNavbarSidebar = (status) => {
    if (!status) {
      document.body.classList.add("sidebar-collapse");
      setClickNavbarSidebar(true);
    } else {
      document.body.classList.remove("sidebar-collapse");
      setClickNavbarSidebar(false);
    }
  };

  return (
    <>
      <aside className="main-sidebar">
        <div className="main-sidebar__warp">
          <nav className="main-sidebar__nav">
            <ul className="nav flex-column">
              {isShowLinkRequestOrder && (
                // request new
                <li className="item" onClick={() => toScreenConstructionInfo()}>
                  <span className="item__link">
                    <img src={iconRequestQuote} alt="icon" />
                    <span>
                      <Trans i18nKey="MAIN_SIDEBAR_NAV_LINK_REQUEST_NEW" />
                    </span>
                  </span>
                </li>
              )}

              {/* List Construction */}
              <li className="item">
                <Link
                  className="item__link"
                  to={{
                    pathname: LinkName.LIST_CONSTRUCTION,
                    state: { search_keyword: "" },
                  }}
                >
                  <img src={iconRequestQuote} alt="icon" />
                  <span>
                    <Trans i18nKey="MAIN_SIDEBAR_NAV_LINK_LIST_CONSTRUCTION" />
                  </span>
                </Link>
              </li>
            </ul>
          </nav>

          <div
            className="main-sidebar__bars"
            onClick={() => handleNavbarSidebar(isClickNavbarSidebar)}
          >
            <FontAwesomeIcon icon={faChevronRight} className="fas icon-right" />
            <FontAwesomeIcon icon={faChevronLeft} className="fas icon-left" />
          </div>
        </div>
      </aside>
    </>
  );
}

export default withRouter(MainSidebar);
