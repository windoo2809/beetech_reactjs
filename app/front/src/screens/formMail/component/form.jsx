import React from "react";
import { useTranslation } from "react-i18next";
import { useFormContext } from "react-hook-form";
import { replaceString } from "../../../helpers/helpers";
import validation from "../../../constants/validation";
import Common from "../../../constants/common";

export default function FormMailInput() {
  const [t] = useTranslation();
  const {
    register,
    formState: { errors },
  } = useFormContext();

  return (
    <div className="group-form mb-5">
      <div className="form-group">
        <label>{t("FROM_MAIL_LABEL_NAME")}</label>
        <div className="div">
          <input
            className="form-control"
            type="text"
            placeholder={t("PLACEHOLDER_MAIL_NAME")}
            {...register("name", {
              required: replaceString(t("VALID_006"), [
                t("FROM_MAIL_LABEL_NAME"),
              ]),
              maxLength: {
                value: validation.INPUT_MAX_LENGTH,
                message: replaceString(t("VALID_002"), [
                  t("FROM_MAIL_LABEL_NAME"),
                  validation.INPUT_MAX_LENGTH,
                ]),
              },
            })}
          />
          {errors.name && (
            <p className="text-danger font-weight-bold">
              {errors.name.message}
            </p>
          )}
        </div>
      </div>
      <div className="form-group">
        <label>{t("FROM_MAIL_LABEL_COMPANY_NAME")}</label>
        <div className="div">
          <input
            type="text"
            {...register("company_name", {
              required: replaceString(t("VALID_006"), [
                t("FROM_MAIL_LABEL_COMPANY_NAME"),
              ]),
              maxLength: {
                value: validation.INPUT_MAX_LENGTH,
                message: replaceString(t("VALID_002"), [
                  t("FROM_MAIL_LABEL_COMPANY_NAME"),
                  validation.INPUT_MAX_LENGTH,
                ]),
              },
            })}
            className="form-control"
            placeholder={t("PLACEHOLDER_MAIL_COMPANY_NAME")}
          />
          {errors.company_name && (
            <p className="text-error d-block error-active">
              {errors.company_name.message}
            </p>
          )}
        </div>
      </div>
      <div className="form-group">
        <label>{t("FROM_MAIL_LABEL_EMAIL")}</label>
        <div className="div">
          <input
            {...register("mail_address", {
              required: t("VALID_016"),
              pattern: {
                value: new RegExp(validation.EMAIL.PATTERN),
                message: t("VALID_017"),
              },
              maxLength: {
                value: validation.INPUT_EMAIL_MAX_LENGTH,
                message: replaceString(t("VALID_049"), [
                  validation.INPUT_EMAIL_MAX_LENGTH,
                ]),
              },
            })}
            className="form-control"
            placeholder={t("PLACEHOLDER_MAIL_ADDRESS")}
          />
          {errors.mail_address && (
            <p className="text-error d-block error-active">
              {errors.mail_address.message}
            </p>
          )}
        </div>
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
              value={Common.INQUIRY_TYPE.SERVICE}
              defaultChecked
            />
            <label htmlFor="option-1" className="form-check-label">
              {t("FROM_MAIL_LABEL_INQUIRY_TYPE_SERVICE")}
            </label>
          </div>
          <div className="form-check">
            <input
              id="option-2"
              className="form-check-input"
              type="radio"
              {...register("inquiry_type")}
              value={Common.INQUIRY_TYPE.USING}
            />
            <label htmlFor="option-2" className="form-check-label">
              {t("FROM_MAIL_LABEL_INQUIRY_TYPE_USE")}
            </label>
          </div>

          <div className="form-check">
            <input
              id="option-3"
              className="form-check-input"
              type="radio"
              {...register("inquiry_type")}
              value={Common.INQUIRY_TYPE.OTHER_QUESTIONS}
            />
            <label htmlFor="option-3" className="form-check-label">
              {t("FROM_MAIL_LABEL_INQUIRY_TYPE_OTHER")}
            </label>
          </div>
        </div>
      </div>
      <div className="form-group align-items-start">
        <label htmlFor="">{t("FROM_MAIL_LABEL_CONTENT")}</label>
        <div className="div">
          <textarea
            {...register("content", {
              required: replaceString(t("VALID_006"), [
                t("WEG_05_0103_content"),
              ]),
              maxLength: {
                value: validation.TEXTAREA_MAX_LENGTH,
                message: replaceString(t("VALID_002"), [
                  t("WEG_05_0103_content"),
                  validation.TEXTAREA_MAX_LENGTH,
                ]),
              },
            })}
            id="5"
            className="form-control"
            rows="4"
            placeholder={t("PLACEHOLDER_MAIL_CONTENT")}
          ></textarea>
          {errors.content && (
            <p className="text-error d-block error-active">
              {errors.content.message}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
