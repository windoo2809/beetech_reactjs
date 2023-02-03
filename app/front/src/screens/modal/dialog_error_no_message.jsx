import React from "react";
import { Modal } from "reactstrap";
import {useTranslation } from "react-i18next";
import LinkName from "../../constants/link_name";
import "../../assets/scss/screens/modal.scss";
import { Link } from "react-router-dom";


function DialogErrorNoMessage(props){
    const [ t ] = useTranslation();
    const {text} = props;
    return(
        <Modal
            isOpen={props.modal}
            className="dialog_back_top"
        >
            <div className="text-center">
                {text}
            </div>
            <div className="text-center box-modal-action">
                <Link to={LinkName.LOGIN} className="btn btn-lg btn-primary">OK</Link>
            </div>
        </Modal>
    );
}
export default DialogErrorNoMessage;