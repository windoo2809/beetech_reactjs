import React from "react";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { Modal } from "reactstrap";
import "../../assets/scss/screens/modal.scss";
import LinkName from "../../constants/link_name";

function DialogFormMailSuccess(props) {
  const [t] = useTranslation();

  return (
    <Modal isOpen={props.dialogSendMailSuccess} className="dialog_form_mail">
      <div className="text-center">{t("FROM_MAIL_TEXT_DIALOG_SENT")}</div>
      <div className="text-center box-modal-action">
        <Link
          to={LinkName.FORM_MAIL}
          className="btn btn-lg btn-primary"
          onClick={() => {
            props.handleSuccess();
          }}
        >
          OK
        </Link>
      </div>
    </Modal>
  );
}
export default DialogFormMailSuccess;
