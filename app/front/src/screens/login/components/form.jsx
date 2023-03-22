import React from "react";
import { useFormContext } from "react-hook-form";
import { useTranslation } from "react-i18next";
import LinkName from "../../../constants/link_name";
import validation from "../../../constants/validation";

export default function LoginInput() {
  const [t] = useTranslation();

  const {
    register,
    formState: { errors },
  } = useFormContext();

  return (
    <div className="group-box">
      <div className="form-group">
        <div className="form-group__control">
          <div className="reset-box">
            <input
              type="text"
              className="form-group__field form-control"
              placeholder={t("WEG_01_0100_enterLoginID")}
              {...register("loginID", {
                required: t("VALID_014"),
                pattern: {
                  value: validation.EMAIL.PATTERN,
                  message: t("VALID_015"),
                },
              })}
            />
            {errors.loginID && (
              <p className="text-danger font-weight-bold">
                {errors.loginID.message}
              </p>
            )}
          </div>
        </div>
      </div>
      <div className="form-group">
        <div className="form-group__control">
          <div className="reset-box">
            <input
              type="text"
              className="form-group__field form-control"
              placeholder={t("WEG_01_0100_placeholder_customer_login_id")}
              {...register("customer_login_id", {
                required: t("CUSTOMER_ID_LOGIN"),
              })}
            />
            {errors.customer_login_id && (
              <p className="text-danger font-weight-bold">
                {errors.customer_login_id.message}
              </p>
            )}
          </div>
        </div>
      </div>
      <div className="form-group">
        <div className="form-group__control">
          <div className="reset-box">
            <input
              type="password"
              className="form-group__field form-control"
              placeholder={t("WEG_01_0100_enterLoginPassword")}
              {...register("password", {
                required: t("VALID_018"),
              })}
            />
            {errors.password && (
              <p className="text-danger font-weight-bold">
                {errors.password.message}
              </p>
            )}
          </div>
        </div>
      </div>
      <div className="login-page__reset-pass">
        <a href={LinkName.SEND_URL_RE_SETTING_PASSWORD}>
          {t("WEG_01_0100_forgotPassword")}
        </a>
      </div>
    </div>
  );
}
