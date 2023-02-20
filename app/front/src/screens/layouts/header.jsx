import React, {useEffect, useState} from 'react';
import '../../assets/scss/screens/layouts/header_ver_2.scss';
import SmallAuth from "./header/small_auth";
import NavLink from "./header/nav_link";
import {isEmpty, isFunction} from 'underscore';
import {Link, withRouter} from "react-router-dom";
import logo from "../../assets/images/login.svg";
import ImageIconHelp from "../../assets/images/icon/icon-help.png";
import ImageIconFAQ from "../../assets/images/icon/icon-faq.png";
import ImageIconBell from "../../assets/images/icon/icon-bell.png";
import LinkName from "../../constants/link_name";
import {Container} from "react-bootstrap";

function Header(props) {
    const {messageProjectId, getInfoUser, setDbUserInfo, hasHeader, hasNav, isUserRole, isUserName, isShowBranchList, isTotalInfoUnread, isShowMenu} = props;
    const [isShowNav, setIsShowNav] = useState(false);
    const handleClickShowHideNav = () => setIsShowNav(!isShowNav);

    // Handle load data default
    useEffect(() => {
        let isMounted = true;

        if ( isMounted ) {

            if ( Object.keys(getInfoUser).length > 0 ) {
                if (isFunction(setDbUserInfo)) {
                    setDbUserInfo(getInfoUser);
                }
            }

        }

        return () => { isMounted = false };

    }, [setDbUserInfo, getInfoUser]);

    return (
        hasHeader && (
            <>
                <header className="site-header">
                    <Container>
                        <div className="site-header__box">
                            <div className="logo">
                                <Link to={LinkName.TOP}>
                                    <img src={logo} className="header-logo" alt="logo"/>
                                </Link>
                            </div>

                            <div className="search-box d-none d-lg-flex align-items-lg-center">
                                {( isUserName !== null && isShowMenu ) }
                            </div>

                            <div className="right-box">
                                <SmallAuth
                                    userName={isUserName}
                                    isShowBranchList={isShowBranchList}
                                />

                                <ul className="nav-link">
                                    <li>
                                        <Link to={LinkName.FORM_MAIL}>
                                            <img src={ImageIconHelp} alt="help"/>
                                        </Link>
                                    </li>

                                    <li>
                                        <Link to={'#'}>
                                            <img src={ImageIconFAQ} alt="faq"/>
                                        </Link>
                                    </li>

                                    {/* Notification list */}
                                    <li className="notification d-none d-lg-block">
                                        <Link to={LinkName.LIST_NOTIFICATION}>
                                            <img src={ImageIconBell} alt="bell"/>

                                            {isTotalInfoUnread > 0 && (
                                                <span className="count-info">{isTotalInfoUnread}</span>
                                            )}
                                        </Link>
                                    </li>
                                </ul>

                                {( isUserName !== null && hasNav && isShowMenu ) && (
                                    <NavLink
                                        showNav={isShowNav}
                                        onClickCloseNav={handleClickShowHideNav}
                                        userRole={isUserRole}
                                        messageProjectId={!isEmpty(messageProjectId) ? messageProjectId : null}
                                        history={props.history}
                                    />
                                )}
                            </div>
                        </div>
                    </Container>
                </header>
            </>
        )
    );
}

export default withRouter(Header);