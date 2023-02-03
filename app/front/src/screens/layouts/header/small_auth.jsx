import React, { useState } from 'react';
import {Dropdown} from "react-bootstrap";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faChevronDown, faSignOutAlt} from "@fortawesome/free-solid-svg-icons";
import LinkName from "../../../constants/link_name";
import {Link, withRouter} from 'react-router-dom';
import { clearLoginData } from '../../../helpers/helpers';
import logoutApi from '../../../api/logoutApi';
import DialogBackToLogin from '../../modal/dialog_back_to_login';
import LoadingOverlay from '../loading_overlay';
import { Trans } from "react-i18next";
import { getInfoUserLogin } from '../../../helpers/helpers';

function SmallAuth(props) {
    const { userName, isShowBranchList } = props;
    const userData = getInfoUserLogin();

    const [isLoadingOverlay, setIsLoadingOverlay] = useState(false);
    const [modalDialogBackToLogin, setModalDialogBackToLogin] = useState(false);
    const toggleModalDialogBackToLogin = () => {
        setModalDialogBackToLogin(!modalDialogBackToLogin);
    };

    // event logout
    const handleSignOut = () => {
        setIsLoadingOverlay(true);
        logoutApi.put().then( () => {
            setIsLoadingOverlay(false);
            clearLoginData();
            props.history.push(LinkName.LOGIN);
        }, () => {
            setIsLoadingOverlay(false);
            clearLoginData();
            props.history.push(LinkName.LOGIN);
        });
    };

    return (
        <>
            {Object.keys(userData).length !== 0 ? (
                <>
                    <Dropdown className="user-nav">
                        <Dropdown.Toggle className="user-nav__name" id="dropdown-user">
                            <span className="mr-2">{ userName }</span>
                            <FontAwesomeIcon icon={faChevronDown} className="fas" />
                        </Dropdown.Toggle>

                        <Dropdown.Menu className="user-nav__mange">
                            {isShowBranchList && (
                                <Dropdown.Item href={LinkName.SELECT_BRANCH}>
                                    <Trans i18nKey="WCG_9000_backToChooseBranchList" />
                                </Dropdown.Item>
                            )}

                            <Dropdown.Item className="logout" href="#" onClick={handleSignOut}>
                                <FontAwesomeIcon icon={faSignOutAlt} />
                                <span className="ml-2"><Trans i18nKey="WCG_9000_logout" /></span>
                            </Dropdown.Item>
                        </Dropdown.Menu>
                    </Dropdown>

                    <DialogBackToLogin modal={modalDialogBackToLogin} toggle={toggleModalDialogBackToLogin} />
                    {isLoadingOverlay && <LoadingOverlay/>}
                </>
            ) : (
                <div className="login-box">
                    <Link to={LinkName.PAGE_LOGIN}>
                        <Trans i18nKey="WCG_9000_SignIn" />
                    </Link>
                </div>
            )}

        </>
    );
}

export default withRouter(SmallAuth);