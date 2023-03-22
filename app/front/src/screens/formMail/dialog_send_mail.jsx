import React from "react";
import { useTranslation } from "react-i18next";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";

export default function DialogSendMail(props) {
  const [t] = useTranslation();

  return (
    <Modal isOpen={props.modal} className="dialog_form_mail">
      <div className="text-center">{t("FROM_MAIL_TEXT_DIALOG_SEND")}</div>
      <div className="text-center box-modal-action">
        <button
          className="btn btn-lg btn-primary"
          onClick={props.handleClickSendMail}
        >
          {t("LABEL_OK")}
        </button>

        <button className="btn btn-lg btn-secondary" onClick={props.onCancel}>
          {t("WEG_05_0103_cancel")}
        </button>
      </div>
    </Modal>
  );
}
