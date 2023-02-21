import React from "react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faBars,
  faHome,
  faPhoneVolume,
} from "@fortawesome/free-solid-svg-icons";
import { Link } from "react-router-dom";
import LinkName from "../../../constants/link_name";

function TopLeft(props) {
  const { hasNav } = props;
  return (
    <>
      {hasNav && (
        <>
          <button className="open-header-nav" onClick={props.onClickShowNav}>
            <FontAwesomeIcon icon={faBars} />
          </button>
          <Link to={LinkName.TOP} className="header__home">
            <FontAwesomeIcon icon={faHome} />
          </Link>
        </>
      )}

      <h1 className="header__logo">
        <span>LANDMARK</span>
        <span>顧客向けシステム</span>
      </h1>
    </>
  );
}

export default TopLeft;
