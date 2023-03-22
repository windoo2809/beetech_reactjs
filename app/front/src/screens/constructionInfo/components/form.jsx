import React from "react";
import { useTranslation } from "react-i18next";
import { useFormContext } from "react-hook-form";
import { replaceString } from "../../../helpers/helpers";
import validation from "../../../constants/validation";

export default function ConstructionInfoInput(props) {
  const [t] = useTranslation();
  const { isBtnSearchZip, handleZipcode, handleChecked, isDisabled } = props;
  const {
    register,
    formState: { errors },
  } = useFormContext();

  return (
    <div className="construction-information__box card-body">
      <div className="box-group">
        <div className="d-block flex-wrap form-group">
          <div className="form-group__label">
            <div className="form-label">
              {t("WEG_03_0010_label_construction_number")}
            </div>
          </div>
          <div className="form-group__control">
            <div className="reset-box">
              <input
                className="form-control"
                type="text"
                {...register("construction_number", {
                  required: {
                    value: true,
                    message: replaceString(t("VALID_006"), ["工事番号"]),
                  },
                  maxLength: {
                    value: validation.INPUT_MAX_LENGTH,
                    message: replaceString(t("VALID_007"), ["工事番号", "255"]),
                  },
                })}
                placeholder={t("WEG_03_0010_placeholder_construction_number")}
              />
            </div>
            {errors.construction_number && (
              <p className="text-error font-weight-bold">
                {errors.construction_number.message}
              </p>
            )}
          </div>
        </div>
        <div className="d-block flex-wrap form-group">
          <div className="form-group__label">
            <label className="form-label">
              {t("WEG_03_0010_label_construction_name")}
            </label>
            <span className="required">{t("WEG_03_0010_required")}</span>
          </div>
          <div className="form-group__control">
            <div className="reset-box">
              <input
                className="form-control"
                type="text"
                {...register("construction_name", {
                  required: {
                    value: true,
                    message: replaceString(t("VALID_006"), ["現場名"]),
                  },
                  maxLength: {
                    value: validation.INPUT_MAX_LENGTH,
                    message: replaceString(t("VALID_007"), ["現場名", "255"]),
                  },
                })}
                placeholder={t("WEG_03_0010_placeholder_construction_name")}
              />
            </div>
            {errors.construction_name && (
              <p className="text-danger font-weight-bold">
                {errors.construction_name.message}
              </p>
            )}
          </div>
        </div>
        <div className="d-block flex-wrap form-group">
          <div className="form-group__label">
            <label className="form-label">
              {t("WEG_03_0010_label_post_code")}
            </label>
          </div>
          <div className="form-group__control">
            <div className="group-zip">
              <div className="reset-box flex-grow-1 flex-shrink-1">
                <input
                  className="form-control"
                  type="text"
                  {...register("zipcode", {
                    required: true,
                    pattern: {
                      value: validation.ZIP_CODE.PATTERN,
                      message: t("VALID_004"),
                    },
                  })}
                  placeholder={t("WEG_03_0010_placeholder_post_code")}
                />
              </div>
              {isBtnSearchZip && (
                <button className="btn btn-search-zip" onClick={handleZipcode}>
                  {t("WEG_03_0010_text_btn_search_zip")}
                </button>
              )}
            </div>
            <div className="form-check">
              <input
                type="checkbox"
                id="checkSearchZip"
                name="check_search_zip"
                className="form-check-input"
                onClick={handleChecked}
              />
              <label htmlFor="checkSearchZip" className="form-check-label">
                {t("WEG_03_0010_text_check_search_zip")}
              </label>
            </div>
            {errors.zipcode && (
              <p className="text-danger font-weight-bold">
                {errors.zipcode.message}
              </p>
            )}
          </div>
        </div>
        <div className="d-block flex-wrap form-group">
          <div className="form-group__label">
            <label className="form-label">
              {t("WEG_03_0010_label_prefectures")}
            </label>
            <span className="required">{t("WEG_03_0010_required")}</span>
          </div>
          <div className="form-group__control form-group__readonly">
            <div className="reset-box">
              <input
                className="form-control input-hidden-attribute"
                tabIndex="-1"
                type="text"
                {...register("site_prefecture", {
                  required: replaceString(t("VALID_006"), ["都道府県"]),
                })}
                placeholder={t("WEG_03_0010_select_prefecture_default")}
                readOnly={isDisabled}
              />
            </div>
            {errors.site_prefecture && (
              <p className="text-danger font-weight-bold">
                {errors.site_prefecture.message}
              </p>
            )}
          </div>
        </div>
        <div className="d-block flex-wrap form-group">
          <div className="form-group__label">
            <label className="form-label">{t("WEG_01_0100_label_city")}</label>
            <span className="required">{t("WEG_03_0010_required")}</span>
          </div>
          <div className="form-group__control">
            <div className="reset-box">
              <input
                className="form-control"
                type="text"
                {...register("site_city", {
                  required: replaceString(t("VALID_006"), ["市区町村"]),
                  maxLength: {
                    value: validation.INPUT_MAX_LENGTH,
                    message: replaceString(t("VALID_007"), ["市区町村", "255"]),
                  },
                })}
                placeholder={t("WEG_03_0010_placeholder_city")}
                readOnly={isDisabled}
              />
            </div>
          </div>
          {errors.site_city && (
            <p className="text-danger font-weight-bold">
              {errors.site_city.message}
            </p>
          )}
        </div>
        <div className="d-block flex-wrap form-group">
          <div className="form-group__label">
            <label className="form-label">
              {t("WEG_03_0010_label_address")}
            </label>
            <span className="required">{t("WEG_03_0010_required")}</span>
          </div>
          <div className="form-group__control">
            <div className="reset-box">
              <textarea
                className="form-control form-address"
                rows="2"
                type="text"
                {...register("site_address", {
                  required: replaceString(t("VALID_006"), ["番地（町名以降)"]),
                  maxLength: {
                    value: validation.INPUT_MAX_LENGTH,
                    message: replaceString(t("VALID_007"), [
                      "番地（町名以降)",
                      "255",
                    ]),
                  },
                })}
                placeholder={t("WEG_03_0010_placeholder_address")}
              ></textarea>
            </div>
            {errors.site_address && (
              <p className="text-danger font-weight-bold">
                {errors.site_address.message}
              </p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
