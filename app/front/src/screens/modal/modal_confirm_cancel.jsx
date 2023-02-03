import React from "react";
import { Modal } from "reactstrap";
import {useTranslation,Trans } from "react-i18next";
import "../../assets/scss/screens/modal.scss";
import {Link, withRouter} from "react-router-dom";


function ModalConfirmCancel(props){
    const [ t ] = useTranslation();

    /**
     * get property
     */
    const {modal, toggle, url} = props;

    return(
        <Modal
            isOpen={modal}
            className="dialog_back_top"
        >
            <div className="text-center">
                <Trans i18nKey="CLT0011-I" />
            </div>
            <div className="text-center box-modal-action">
                <button className="btn btn-lg btn-secondary" onClick={toggle}><Trans i18nKey="WEG_03_0102_confirmCancel" /></button>
                <Link to={url} className="btn btn-lg btn-primary" onClick={props.callBack}>OK</Link>
            </div>
        </Modal>
    );
}
export default withRouter(ModalConfirmCancel);