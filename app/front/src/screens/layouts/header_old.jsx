import React, { useEffect, useState } from 'react';
import '../../assets/scss/screens/layouts/header.scss';
import { useTranslation } from "react-i18next";
import TopLeft from "./header/top_left";
import SmallAuth from "./header/small_auth";
import NavLink from "./header/nav_link";
import {getUserFromAccessToken } from "../../helpers/helpers";
import { isEmpty,isFunction } from 'underscore';
import DialogBackToLogin from '../modal/dialog_back_to_login';
import { withRouter } from "react-router-dom";

function Header(props) {
    const { messageProjectId, setDbUserInfo,hasNav, dbUserInfo } = props;

    const [ t ] = useTranslation();
    const [isShowNav, setIsShowNav] = useState(false);
    const handleClickShowHideNav = () => setIsShowNav(!isShowNav);
    const [userName, setUserName] = useState();
    const [userRole, setUserRole] = useState();
    const [errorText,setErrorText] = useState();
    const [modalDialogBackToLogin, setModalDialogBackToLogin] = useState(false);
    const toggleModalDialogBackToLogin = () => {
        setModalDialogBackToLogin(!modalDialogBackToLogin);
    };

    /**
     * Get info user from access token
     */
    const getInfoUser = () => {
        let loginUserInfo = getUserFromAccessToken();
        if (Object.keys(loginUserInfo).length > 0) {
            setUserName(loginUserInfo.customer_user_name);
            setUserRole(loginUserInfo.role);
            
            // set user info
            if (isFunction(setDbUserInfo)) {
                setDbUserInfo(loginUserInfo);
            }
        }
    }

    /**
     * Handle load data default
     */
    useEffect(() => {
        getInfoUser();
    }, []);

    return (
        <>
        {props.hasHeader && (
            <header className="header">
                <div className="header__middle">
                    <div className="container">
                        <div className="position-relative text-center">
                            <TopLeft onClickShowNav={handleClickShowHideNav} hasNav={hasNav} />
                            <SmallAuth userName={userName} userRole={userRole}/>
                        </div>
                    </div>
                </div>

                { hasNav && (
                    <div className="header__bottom">
                        <div className="container">
                            <NavLink
                                showNav={isShowNav}
                                onClickCloseNav={handleClickShowHideNav}
                                userRole={userRole}
                                messageProjectId={!isEmpty(messageProjectId) ? messageProjectId : null}
                                history = {props.history}
                            />
                        </div>
                    </div>
                )}

                <DialogBackToLogin modal={modalDialogBackToLogin} toggle={toggleModalDialogBackToLogin} text={errorText} />
            </header>
        )}
        </>
    );
}

export default withRouter(Header);