import React from "react";
import { useTranslation } from "react-i18next";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";

function DialogFormMailSuccess(props) {
  const [t] = useTranslation();

  return (
    <Modal isOpen={props.dialogSendMailSuccess} className="dialog_form_mail">
      <div className="text-center">{t("FROM_MAIL_TEXT_DIALOG_SENT")}</div>
      <div className="text-center box-modal-action">
        <button
          className="btn btn-lg btn-primary"
          onClick={() => {
            props.handleSuccess();
          }}
        >
          OK
        </button>
      </div>
    </Modal>
  );
}
export default DialogFormMailSuccess;
