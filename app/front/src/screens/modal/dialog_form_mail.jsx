import React from "react";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";
import LinkName from "../../constants/link_name";

function DialogFormMail(props) {
  const [t] = useTranslation();

  return (
    <Modal isOpen={props.dialogSendMail} className="dialog_form_mail">
      <div className="text-center">{t("FROM_MAIL_TEXT_DIALOG_SEND")}</div>
      <div className="text-center box-modal-action">
        <button
          className="btn btn-lg btn-primary"
          onClick={() => {
            props.handleClick();
          }}
        >
          OK
        </button>

        <button
          className="btn btn-lg btn-secondary"
          onClick={() => {
            props.setDialogSendMail(false);
          }}
        >
          {t("WEG_05_0103_cancel")}
        </button>
      </div>
    </Modal>
  );
}
export default DialogFormMail;
