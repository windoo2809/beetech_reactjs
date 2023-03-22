import React from "react";
import { Trans } from "react-i18next";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";

export default function DialogMessage(props) {
  const { modal, message, btnCancel, onCancel, onOk } = props;
  return (
    <Modal isOpen={modal} className="dialog_message_back">
      <div className="text-center">{message}</div>
      <div className="text-center box-modal-action">
        {btnCancel && (
          <button className="btn btn-lg btn-secondary" onClick={onCancel}>
            <Trans i18nKey="WEG_05_0103_cancel" />
          </button>
        )}
        <button className="btn btn-lg btn-primary" onClick={onOk}>
          <Trans i18nKey="LABEL_OK" />
        </button>
      </div>
    </Modal>
  );
}
