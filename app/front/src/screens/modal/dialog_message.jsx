import React from "react";
import { Trans, useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";
import LinkName from "../../constants/link_name";

function DialogMessage(props){
    const [ t ] = useTranslation();
    return(
        <Modal 
            isOpen={props.modal} 
            className="dialog_message_back"
        >   
            <div className="text-center">
            <Trans i18nKey="CLT0011-I" />
            </div>
            <div className="text-center box-modal-action">
                <button onClick={props.toggle} className="btn btn-lg btn-secondary"><Trans i18nKey="WEG_05_0103_cancel" /></button>
                <Link to={LinkName.DETAIL_REQUEST} className="btn btn-lg btn-primary">OK</Link>
            </div>
        </Modal>
    );
}
export default DialogMessage;
