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
import branchListApi from "../../api/branchListApi";
import Common from "../../constants/common";
import {clearLoginData, getInfoUserLogin, getUserFromAccessToken, replaceString} from "../../helpers/helpers";
import LinkName from "../../constants/link_name";
import DialogReload from "../modal/dialog_reload";
import {Trans, useTranslation} from "react-i18next";
import {Button, Container, Modal} from "react-bootstrap";
import Breadcrumb from "./breadcrumb"
import MainSidebar from "./main_sidebar";
import detailRequestApi from "../../api/detailRequestApi";
import listNotificationApi from "../../api/listNotificationApi";

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
    const checkShowButtonBackToBranchList = () => {
        branchListApi.get(headersConfig).then((response) => {

            if (response.data.status === Common.HTTP_STATUS.OK) {
                if (response.data.data.length > 1) {
                    setShowBranchList(true);
                } else {
                    setShowBranchList(false);
                }
            }
            else{
                setErrorText(t('WCG_9000_getBranchListError'));
                toggleModalDialogError();
            }

        }, (error) => {

            if (error.response && error.response.status === Common.HTTP_STATUS.UNAUTHORIZED) {
                clearLoginData();
                props.history.push(LinkName.LOGIN);
            } else if(error.response && error.response.status === Common.HTTP_STATUS.FORBIDDEN){
                setErrorText(t('WCG_9000_getDataAccess'));
                toggleModalDialogError();
            } else {
                setErrorText(t('WCG_9000_getBranchListError'));
                toggleModalDialogError();
            }

        });

    }

    useEffect( () => {
        let isMounted = true;

        if ( isMounted ) {

            if( isUserRole ) {
                if(!screensNotCallApi.includes(pathname)){
                    checkShowButtonBackToBranchList();
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

        if ( isMounted ) {
            if ( Object.keys(loginUserInfo).length > 0 && props.hasMainSidebar ) {
                detailRequestApi.getCustomerOption({}, headersConfig).then((response) => {
                    const status = response.status;
                    const data = response.data.data;

                    if ( status === Common.HTTP_STATUS.OK ) {
                        sessionStorage.setItem("approval", data.approval);
                    }

                }, (error) => {
                    const statusError = error.response.status;

                    if ( statusError === Common.HTTP_STATUS.UNAUTHORIZED ) {
                        clearLoginData();
                        props.history.push(LinkName.LOGIN);
                    } else if ( statusError === Common.HTTP_STATUS.FORBIDDEN ) {
                        setStatusError(true);
                        setNameActionError( t('WEG_08_0202_get_data_by_auth') );
                    } else {
                        setStatusError(true);
                        setNameActionError( replaceString(t('CMN0006-W'), [t('WEG_07_0101_text_error_applications')]) );
                    }
                })
            }
            if ( Object.keys(loginUserInfo).length > 0 && Object.keys(userData).length > 0 && !screensNotCallApi.includes(pathname)) {
                listNotificationApi.getListNotification({
                    params: {
                        display_header: '',
                        display_advertisement: '',
                        page: '',
                        limit: '',
                        read: 0,
                        unread: 1
                    }
                }, headersConfig).then((response) => {
                    const status = response.status;
                    const data = response.data.data;

                    if ( status === Common.HTTP_STATUS.OK && data ) {
                        const resultsInfoUnread = data.filter(item => {
                            return checkInfoUnread.includes( item.already_read );
                        });

                        if ( resultsInfoUnread.length > 0 ) {
                            setTotalInfoUnread(resultsInfoUnread.length);
                        }

                    }

                }, (error) => {

                    if (error.response && error.response.status === Common.HTTP_STATUS.UNAUTHORIZED) {
                        clearLoginData();
                        props.history.push(LinkName.LOGIN);
                    } else if (error.response && error.response.status === Common.HTTP_STATUS.FORBIDDEN) {
                        setStatusError(true);
                        setNameActionError( t('WEG_08_0202_get_data_by_auth') );
                    } else {
                        setStatusError(true);
                        setNameActionError( replaceString(t('CMN0006-W'), [t('TEXT_ERROR_GET_NUMBER_NOTIFICATIONS')]) );
                    }

                })

                if ( !pathNameNotShow.includes( location.pathname ) ) {
                    setShowMenu(true);
                }
            }
        }

        return () => { isMounted = false };
    }, [])

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

            <DialogReload
                modal={modalDialogError}
                toggle={toggleModalDialogError}
                text={errorText}
                status={false}
                history={props.history}
            />

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