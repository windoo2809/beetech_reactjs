import React from "react";
import { Dropdown } from "react-bootstrap";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faBars,
  faCog,
  faLock,
  faTasks,
  faUser,
} from "@fortawesome/free-solid-svg-icons";
import LinkName from "../../../constants/link_name";
import Common from "../../../constants/common";
import { Trans } from "react-i18next";
import { getInfoUserLogin } from "../../../helpers/helpers";

function NavLink(props) {
  // data props
  const { userRole } = props;
  const listUserManagerment = [
    Common.USER.ROLE.SUPER_USER,
    Common.USER.ROLE.SYSTEM_ADMIN,
  ];

  //get userData
  const userData = getInfoUserLogin();

  return (
    <>
      <Dropdown className="menu">
        <Dropdown.Toggle className="icon-drop" id="dropdown-basic">
          <FontAwesomeIcon icon={faBars} className="fas" />
        </Dropdown.Toggle>

        <Dropdown.Menu className="menu__drop" alignRight={true}>
          {listUserManagerment.includes(userRole) && (
            <>
              <Dropdown.Item href={LinkName.USER_LIST}>
                <FontAwesomeIcon icon={faUser} />
                <Trans i18nKey="WCG_9000_userManagement" />
              </Dropdown.Item>

              <Dropdown.Item href={LinkName.SYSTEM_USAGE_STATUS}>
                <FontAwesomeIcon icon={faCog} />
                <Trans i18nKey="WCG_9000_systemUsage" />
              </Dropdown.Item>
            </>
          )}

          {/* Payment request */}
          {Object.keys(userData).length !== 0 && (
            <Dropdown.Item href={LinkName.PAYMENT_REQUEST}>
              <FontAwesomeIcon icon={faTasks} />
              <Trans i18nKey="WCG_9000_paymentRequest" />
            </Dropdown.Item>
          )}

          {/* Change password */}
          {Object.keys(userData).length !== 0 && (
            <Dropdown.Item href={LinkName.CHANGE_PASSWORD}>
              <FontAwesomeIcon icon={faLock} />
              <Trans i18nKey="WCG_9000_changePassword" />
            </Dropdown.Item>
          )}
        </Dropdown.Menu>
      </Dropdown>
    </>
  );
}

export default NavLink;
