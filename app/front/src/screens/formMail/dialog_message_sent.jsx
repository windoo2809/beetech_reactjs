import React from "react";
import { Trans } from "react-i18next";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";

function DialogMessage(props) {
    return (
        <Modal isOpen={props.modal} className="dialog_message_back">
            <div className="text-center">{props.message}</div>
            <div className="text-center box-modal-action">
                <button className="btn btn-lg btn-primary" onClick={props.onOk}>
                    <Trans i18nKey="LABEL_OK" />
                </button>
            </div>
        </Modal>
    );
}
export default DialogMessage;
