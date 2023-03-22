import React, { useState } from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { FormProvider, useForm } from "react-hook-form";
import LinkName from "../../constants/link_name";
import Common from "../../constants/common";
import { replaceString } from "../../helpers/helpers";
import DialogSendMail from "./dialog_send_mail";
import DialogMessage from "../../components/modal/modal_common";
import formMail from "../../api/formMail";
import HeaderNew from "../layouts/header_new";
import FooterNew from "../layouts/footer_new";
import FormMailInput from "./component/form";
import "../../assets/scss/screens/form_mail.scss";

export default function FormMail() {
  const [t] = useTranslation();
  const [modalDialogSendMail, setModalDialogSendMail] = useState(false);
  const [modalDialogMessage, setModalDialogMessage] = useState({
    modal: false,
    onOk: () => {},
    message: "",
  });

  const methods = useForm({
    mode: "onChange",
    reValidateMode: "onChange",
    defaultValues: {
      name: "",
      company_name: "",
      mail_address: "",
      content: "",
    },
  });
  const { handleSubmit, getValues, setValue } = methods;

  const handleSubmitForm = () => {
    setModalDialogSendMail(true);
  };

  const callApiFormMail = () => {
    formMail
      .postMail({
        email: getValues("mail_address"),
        password: getValues("content"),
      })
      .then(
        (response) => {
          if (response.status === Common.HTTP_STATUS.OK) {
            setModalDialogMessage({
              modal: true,
              onOk: () => sendFormMailSuccess(),
              message: t("FROM_MAIL_TEXT_DIALOG_SENT"),
            });
            console.log(response);
          } else {
            setModalDialogMessage({
              modal: true,
              onOk: closeDialog,
              message: replaceString(t("CMN0006-W"), [t("ACTION_NAME")]),
            });
          }
        },
        (error) => {
          setModalDialogMessage({
            modal: true,
            onOk: closeDialog,
            message: replaceString(t("CMN0006-W"), [t("ACTION_NAME")]),
          });
        }
      );
  };

  const sendFormMailSuccess = () => {
    setValue("inquiry_type", Common.INQUIRY_TYPE.SERVICE);
    setValue("name", null);
    setValue("company_name", null);
    setValue("mail_address", null);
    setValue("content", null);

    closeDialog();
  };

  const closeDialog = () => {
    setModalDialogMessage((data) => ({ ...data, modal: false }));
    setModalDialogSendMail(false);
  };

  return (
    <>
      <DialogMessage {...modalDialogMessage} />

      <DialogSendMail
        modal={modalDialogSendMail}
        onCancel={() => setModalDialogSendMail(false)}
        handleClickSendMail={callApiFormMail}
      />
      <HeaderNew />
      <div className="sticky-footer not-login">
        <div className="page-template">
          <Container>
            <nav className="breadcrumb-box" aria-label="breadcrumb">
              <ol className="breadcrumb">
                <li className="breadcrumb-item">
                  <a href={LinkName.LOGIN}>ログイン</a>
                </li>
                <li className="breadcrumb-item" aria-current="page">
                  お問合せ
                </li>
              </ol>
            </nav>
            <div className="template-form-mail">
              <div className="wrap">
                <h1 className="title-breadcrumb font-weight-bold">
                  {t("TITLE_FROM_MAIL")}
                </h1>
                <div className="form-mail">
                  <FormProvider {...methods}>
                    <form onSubmit={handleSubmit(handleSubmitForm)}>
                      <FormMailInput />

                      <div className="action-box">
                        <button type="submit" className="btn btn-primary">
                          {t("FROM_MAIL_BUTTON_SUBMIT")}
                        </button>
                      </div>
                    </form>
                  </FormProvider>
                </div>
              </div>
            </div>
          </Container>
        </div>
      </div>
      <FooterNew />
    </>
  );
}
