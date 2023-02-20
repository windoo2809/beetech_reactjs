import React, { useState } from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { useForm } from "react-hook-form";
import { replaceString } from "../helpers/helpers";
import validation from "../constants/validation";
import LinkName from "../constants/link_name";
import DialogFormMail from "./modal/dialog_form_mail";
import DialogFormMailSuccess from "./modal/dialog_form_mail_success";
import formMail from "../api/formMail";
import HeaderNew from "./layouts/header_new";
import FooterNew from "./layouts/footer_new";
import "../assets/scss/screens/form_mail.scss";

function FormMail() {
  const [t] = useTranslation();
  const [dialogSendMail, setDialogSendMail] = useState(false);
  const [dialogSendMailSuccess, setDialogSendMailSuccess] = useState(false);

  const {
    register,
    handleSubmit,
    reset,
    getValues,
    formState: { errors },
  } = useForm({
    shouldUseNativeValidation: true,
  });

  const onSubmit = () => {
    setDialogSendMail(true);
  };

  const handleClick = () => {
    formMail
      .postMail({
        email: getValues("mail_address"),
        password: getValues("content"),
      })
      .then(
        (data) => {
          console.log(data);
        },
        (error) => {
          console.log(error);
        }
      );
    setDialogSendMailSuccess(true);
  };

  const handleSuccess = () => {
    setDialogSendMailSuccess(false);
    setDialogSendMail(false);
    reset();
  };

  return (
    <>
      <DialogFormMailSuccess
        dialogSendMailSuccess={dialogSendMailSuccess}
        setDialogSendMailSuccess={setDialogSendMailSuccess}
        handleSuccess={handleSuccess}
      />
      <DialogFormMail
        dialogSendMail={dialogSendMail}
        setDialogSendMail={setDialogSendMail}
        handleClick={handleClick}
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
                  <form onSubmit={handleSubmit(onSubmit)}>
                    <div className="group-form mb-5">
                      <div className="form-group">
                        <label>{t("FROM_MAIL_LABEL_NAME")}</label>
                        <input
                          className="form-control"
                          type="text"
                          {...register("name", {
                            required: replaceString(t("VALID_006"), ["お名前"]),
                            maxLength: {
                              value: validation.INPUT_MAX_LENGTH,
                              message: replaceString(t("VALID_002"), [
                                "お名前",
                                "225",
                              ]),
                            },
                          })}
                          placeholder={t("PLACEHOLDER_MAIL_NAME")}
                        />
                        <div></div>
                        {errors.name && (
                          <p className="text-error d-block error-active">
                            {errors.name.message}
                          </p>
                        )}
                      </div>
                      <div className="form-group">
                        <label>{t("FROM_MAIL_LABEL_COMPANY_NAME")}</label>
                        <input
                          type="text"
                          {...register("company_name", {
                            required: replaceString(t("VALID_006"), ["会社名"]),
                            maxLength: {
                              value: validation.INPUT_MAX_LENGTH,
                              message: replaceString(t("VALID_002"), [
                                "会社名",
                                "225",
                              ]),
                            },
                          })}
                          className="form-control"
                          placeholder={t("PLACEHOLDER_MAIL_COMPANY_NAME")}
                        />
                        <div></div>
                        {errors.company_name && (
                          <p className="text-error d-block error-active">
                            {errors.company_name.message}
                          </p>
                        )}
                      </div>
                      <div className="form-group">
                        <label>{t("FROM_MAIL_LABEL_EMAIL")}</label>
                        <input
                          type="email"
                          {...register("mail_address", {
                            required: t("VALID_016"),
                            pattern: {
                              value: validation.EMAIL.PATTERN,
                              message: t("VALID_017"),
                            },
                            maxLength: {
                              value: validation.INPUT_EMAIL_MAX_LENGTH,
                              message: replaceString(t("VALID_049"), ["2048"]),
                            },
                          })}
                          className="form-control"
                          placeholder={t("PLACEHOLDER_MAIL_ADDRESS")}
                        />
                        <div></div>
                        {errors.mail_address && (
                          <p className="text-error d-block error-active">
                            {errors.mail_address.message}
                          </p>
                        )}
                      </div>
                      <div className="form-group align-items-start">
                        <label>{t("FROM_MAIL_LABEL_INQUIRY_TYPE")}</label>
                        <div className="form-group__check">
                          <div className="form-check">
                            <input
                              id="option-1"
                              className="form-check-input"
                              type="radio"
                              {...register("inquiry_type")}
                              value="4"
                              defaultChecked/>
                            <label
                              htmlFor="option-1"
                              className="form-check-label"
                            >
                              {t("FROM_MAIL_LABEL_INQUIRY_TYPE_SERVICE")}
                            </label>
                          </div>
                          <div className="form-check">
                            <input
                              id="option-2"
                              className="form-check-input"
                              type="radio"
                              {...register("inquiry_type")}
                              value="5"
                            />
                            <label
                              htmlFor="option-2"
                              className="form-check-label"
                            >
                              {t("FROM_MAIL_LABEL_INQUIRY_TYPE_USE")}
                            </label>
                          </div>

                          <div className="form-check">
                            <input
                              id="option-3"
                              className="form-check-input"
                              type="radio"
                              {...register("inquiry_type")}
                              value="6"
                            />
                            <label
                              htmlFor="option-3"
                              className="form-check-label"
                            >
                              {t("FROM_MAIL_LABEL_INQUIRY_TYPE_OTHER")}
                            </label>
                          </div>
                        </div>
                      </div>
                      <div className="form-group align-items-start">
                        <label htmlFor="">{t("FROM_MAIL_LABEL_CONTENT")}</label>
                        <textarea
                          {...register("content", {
                            required: replaceString(t("VALID_006"), ["本文"]),
                            maxLength: {
                              value: validation.TEXTAREA_MAX_LENGTH,
                              message: replaceString(t("VALID_002"), [
                                "本文",
                                "200000",
                              ]),
                            },
                          })}
                          id="5"
                          className="form-control"
                          rows="5"
                          placeholder={t("PLACEHOLDER_MAIL_CONTENT")}
                        ></textarea>
                        <div></div>
                        {errors.content && (
                          <p className="text-error d-block error-active">
                            {errors.content.message}
                          </p>
                        )}
                      </div>
                    </div>
                    <div className="action-box">
                      <button type="submit" className="btn btn-primary">
                        {t("FROM_MAIL_BUTTON_SUBMIT")}
                      </button>
                    </div>
                  </form>
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

export default FormMail;
