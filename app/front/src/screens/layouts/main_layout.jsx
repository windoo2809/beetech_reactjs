import React, {useEffect, useState} from 'react';
import {useLocation, withRouter} from "react-router-dom";
import {isEmpty} from 'underscore';
import '../../assets/scss/common/_reset.scss';
import '../../assets/scss/common/_common.scss';
import '../../assets/scss/common/_bootstrap_override.scss';
import '../../assets/scss/common/_slick_slider_override.scss';

// import file
import Header from './header';
import MenuMobile from "./menu-mobile";
import Footer from './footer';
import Common from "../../constants/common";
import {clearLoginData, getInfoUserLogin, getUserFromAccessToken, replaceString} from "../../helpers/helpers";
import LinkName from "../../constants/link_name";
import {Trans, useTranslation} from "react-i18next";
import {Button, Container, Modal} from "react-bootstrap";
import Breadcrumb from "./breadcrumb"
import MainSidebar from "./main_sidebar";

function MainLayout(props) {
    const [t] = useTranslation();
    const loginUserInfo = getUserFromAccessToken();
    const userData = getInfoUserLogin();
    // config request headers
    const headersConfig = {
        headers: {
            'Authorization': `Bearer ${userData.access_token}`
        }
    }

    const {messageProjectId, dbUserInfo, isTotalInfoUnreadPath} = props;

    const {pathname} = useLocation();
    useEffect(() => {
        let isMounted = true;

        if ( isMounted ) {
            window.scrollTo(0, 0);
        }

        return () => { isMounted = false };
    }, [pathname]);

    // get user role
    const [isUserRole, setUserRole] = useState(null);
    const [isUserName, setUserName] = useState(null);
    useEffect((  ) => {
        let isMounted = true;

        if (isMounted && Object.keys(loginUserInfo).length) {
            setUserRole(loginUserInfo.role);
            setUserName(loginUserInfo.customer_user_name);
        }

        return () => { isMounted = false };
    }, [loginUserInfo])

    // check status of button back to branch list
    const [isShowBranchList, setShowBranchList] = useState(false);
    const [errorText, setErrorText] = useState();
    const screensNotCallApi = [
        LinkName.SELECT_BRANCH,
        LinkName.SELECT_CUSTOMER,
        LinkName.RE_SETTING_PASSWORD_FIRST_TIME,
        LinkName.RE_SETTING_PASSWORD,
        LinkName.RE_SETTING_PASSWORD_COMPLETE,
        LinkName.RE_SETTING_PASSWORD_SEND_URL_COMPLETE,
        LinkName.RE_SETTING_PASSWORD_SEND_URL,
    ]

    useEffect( () => {
        let isMounted = true;

        if ( isMounted ) {

            if( isUserRole ) {
                if(!screensNotCallApi.includes(pathname)){
                }
            } else {
                setShowBranchList(false);
            }
        }

        return () => { isMounted = false };
    }, [isUserRole]);

    // call api customer option
    const [isStatusError, setStatusError] = useState(false);
    const [isNameActionError, setNameActionError] = useState(null);
    const handleCloseModalError = () => setStatusError(false);

    // check show menu login
    const location = useLocation();
    const pathNameNotShow = [
        LinkName.SELECT_BRANCH,
        LinkName.SELECT_CUSTOMER,
        LinkName.RE_SETTING_PASSWORD
    ]
    const [isShowMenu, setShowMenu] = useState(false);

    // check info unread
    const [isTotalInfoUnread, setTotalInfoUnread] = useState(0);
    const checkInfoUnread = [0];


    useEffect(() => {
        let isMounted = true;

        if ( pathname === LinkName.DETAIL_NOTIFICATION && isTotalInfoUnreadPath ) {
            setTotalInfoUnread(isTotalInfoUnreadPath)
        }

        return () => { isMounted = false };
    }, [isTotalInfoUnreadPath])

    // modal show error
    const [modalDialogError, setModalDialogError] = useState(false);
    const toggleModalDialogError = () => {
        setModalDialogError(!modalDialogError)
    };

    return (
        <>
            <Header
                messageProjectId={!isEmpty(messageProjectId) ? messageProjectId : null}
                setDbUserInfo={props.setDbUserInfo}
                hasHeader={props.hasHeader}
                hasNav={props.hasNav}
                getInfoUser={loginUserInfo}
                dbUserInfo={dbUserInfo}
                isUserRole={isUserRole}
                isUserName={isUserName}
                isShowBranchList={isShowBranchList}
                isTotalInfoUnread={isTotalInfoUnread}
                isShowMenu={isShowMenu}
            />

            <div className={`sticky-footer ${Object.keys(loginUserInfo).length > 0 && isShowMenu ? 'mobile-style' : 'not-login'}`}>
                <div className={`page-template ${props.classPage}`}>
                    <Container>
                        {props.hasBreadcrumb && (
                            <Breadcrumb
                                listBreadcrumb={props.listBreadcrumb}
                                customBreadcrumb={props.customBreadcrumb}
                            />
                        )}

                        {Object.keys(loginUserInfo).length > 0 && props.hasMainSidebar ? (
                            <div className="page-content has-sidebar">
                                <MainSidebar />

                                <div className="content-wrapper">
                                    {props.children}
                                </div>
                            </div>
                        ) : (
                            props.children
                        )}
                    </Container>
                </div>
            </div>

            {props.hasHeader && Object.keys(loginUserInfo).length > 0 && isShowMenu > 0 && (
                <MenuMobile
                    isTotalInfoUnread={isTotalInfoUnread}
                    header={{
                        messageProjectId: !isEmpty(messageProjectId) ? messageProjectId : null,
                        setDbUserInfo: props.setDbUserInfo,
                        hasHeader: props.hasHeader,
                        hasNav: props.hasNav,
                        getInfoUser: loginUserInfo,
                        dbUserInfo: dbUserInfo,
                        isUserRole: isUserRole,
                        isUserName: isUserName,
                        isShowBranchList: isShowBranchList,
                        isTotalInfoUnread: isTotalInfoUnread,
                        isShowMenu: isShowMenu
                    }}
                />
            )}

            {props.hasFooter && (
                <Footer
                    isUserRole={isUserRole}
                    isShowBranchList={isShowBranchList}
                    getInfoUser={loginUserInfo}
                    isShowMenu={isShowMenu}
                />
            )}

            { /* Modal dialog error */ }
            <Modal show={isStatusError} onHide={handleCloseModalError} className="modal-custom-center">
                <Modal.Body>
                    <div className="card-warp">
                        <div className="card border-0">
                            <div className="card-body text-center">
                                <h5 className="card-title mb-5">
                                    {isNameActionError}
                                </h5>

                                <Button variant="primary" className="btn-lg btn-action" onClick={handleCloseModalError}>
                                    <Trans i18nKey="LABEL_OK" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        </>
    );
}

MainLayout.defaultProps = {
    hasHeader: true,
    hasFooter: true,
    hasNav: true,
    classPage: '',
    hasBreadcrumb: true,
    listBreadcrumb: '',
    customBreadcrumb: false,
    hasMainSidebar: true
};

export default withRouter(MainLayout);