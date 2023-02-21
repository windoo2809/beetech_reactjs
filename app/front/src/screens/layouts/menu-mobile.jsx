import React, { useEffect, useRef, useState } from "react";
import { Link, withRouter } from "react-router-dom";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBell, faHome, faSearch } from "@fortawesome/free-solid-svg-icons";
import LinkName from "../../constants/link_name";
import logo from "../../assets/images/login.svg";
import { getUserFromAccessToken } from "../../helpers/helpers";
import { Trans } from "react-i18next";
import { useLocation } from "react-router";
import Header from "./header";

function MenuMobile(props) {
  const loginUserInfo = getUserFromAccessToken();
  const location = useLocation();
  const { isTotalInfoUnread, header } = props;

  // check use login
  const [isCheckLogin, setCheckLogin] = useState(false);
  useEffect(() => {
    let isMounted = true;

    if (isMounted && Object.keys(loginUserInfo).length > 0) {
      setCheckLogin(true);
    }

    return () => {
      isMounted = false;
    };
  }, [loginUserInfo]);

  // event toggle search
  const [isActiveSearch, setActiveSearch] = useState(false);
  const [isKeySearch, setKeySearch] = useState(null);
  const handleToggleClassSearch = () => {
    if (isActiveSearch) {
      document.body.classList.remove("hidde-scroll");
    } else {
      document.body.classList.add("hidde-scroll");
    }

    setActiveSearch(!isActiveSearch);
  };

  useEffect(() => {
    let isMounted = true;

    if (isMounted && location.state) {
      if (location.state.search_keyword) {
        setKeySearch(location.state.search_keyword);
      } else {
        setKeySearch("");
      }
    }

    return () => {
      isMounted = false;
    };
  }, [location]);

  // hidden form search
  useEffect(() => {
    let isMounted = true;

    if (isMounted && (isKeySearch || isKeySearch === "")) {
      setActiveSearch(false);
      document.body.classList.remove("hidde-scroll");
    }

    return () => {
      isMounted = false;
    };
  }, [isKeySearch]);

  // event click outside
  const refMenuMobile = useRef(null);
  useEffect(() => {
    let isMounted = true;

    if (isMounted && isActiveSearch) {
      const handleClickOutside = (event) => {
        if (
          refMenuMobile.current &&
          refMenuMobile.current.contains(event.target)
        ) {
          // handleToggleClassSearch()
        }
      };

      document.addEventListener("click", handleClickOutside);

      return () => {
        document.removeEventListener("click", handleClickOutside);
      };
    }

    return () => {
      isMounted = false;
    };
  }, [isActiveSearch]);

  return (
    <div className="menu-mobile d-lg-none">
      <div
        id="mobile-search-project"
        className={`menu-mobile__search ${isActiveSearch ? "active" : ""}`}
      >
        <Header {...header} hiddenLogoMobile={true} />
        <div className="body_search">
          <h4 className="title">
            <Trans i18nKey="TITLE_SEARCH" />
          </h4>
        </div>
      </div>

      <div className="menu-mobile__box" ref={refMenuMobile}>
        <div className="left">
          {isCheckLogin && (
            <>
              <FontAwesomeIcon
                icon={faSearch}
                className="fas icon"
                onClick={handleToggleClassSearch}
              />
              <span>
                <Trans i18nKey="TITLE_SEARCH" />
              </span>
            </>
          )}
        </div>

        <div className="middle">
          <FontAwesomeIcon icon={faHome} className="fas icon" />

          <Link to={LinkName.TOP}>
            <span>
              <Trans i18nKey="LABEL_HOME" />
            </span>
            <img src={logo} alt="logo" />
          </Link>
        </div>

        <div className="right">
          <Link to={LinkName.LIST_NOTIFICATION}>
            <div className="icon">
              <FontAwesomeIcon icon={faBell} className="fas icon" />
              {isTotalInfoUnread > 0 && (
                <span className="count-info">{isTotalInfoUnread}</span>
              )}
            </div>
            <span>
              <Trans i18nKey="WCG_9000_listNotification" />
            </span>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default withRouter(MenuMobile);
