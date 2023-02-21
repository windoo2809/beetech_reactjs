import React, { useState, useEffect } from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { useForm } from "react-hook-form";
import { replaceString } from "../helpers/helpers";
import { useHistory } from "react-router-dom";
import validation from "../constants/validation";
import HeaderNew from "./layouts/header_new";
import FooterNew from "./layouts/footer_new";
import customer from "../api/customer";
import "../assets/scss/screens/customer.scss";

export default function Customer() {
  const [t] = useTranslation();
  const history = useHistory();
  const [isDisabled, setIsDisabled] = useState(true);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    shouldUseNativeValidation: true,
  });

  useEffect(() => {
    customer
      .getCustomer()
      .then((data) => {
        console.log(data);
      })
      .catch((err) => {
        console.log(err.message);
      });
  }, []);
  const onSubmit = (data) => {
    console.log(data);
  };
  const onClick = () => {
    history.push("/");
  };

  const isCheckbox = () => {
    setIsDisabled(!isDisabled);
  };

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer">
        <div className="page-template">
          <div className="container">
            <div className="page-content">
              <div className="content-wrapper">
                <div className="construction-information create">
                  <div className="card">
                    <div className="heading">
                      <h1 className="title-page">
                        {t("WEG_03_0010_sub_title_page")}
                      </h1>
                    </div>
                    <form
                      onSubmit={handleSubmit(onSubmit)}
                      className="form-construction-information"
                    >
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
                                      message: replaceString(t("VALID_006"), [
                                        "工事番号",
                                      ]),
                                    },
                                    maxLength: {
                                      value: validation.INPUT_MAX_LENGTH,
                                      message: replaceString(t("VALID_007"), [
                                        "工事番号",
                                        "255",
                                      ]),
                                    },
                                  })}
                                  placeholder={t(
                                    "WEG_03_0010_placeholder_construction_number"
                                  )}
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
                              <span className="required">
                                {t("WEG_03_0010_required")}
                              </span>
                            </div>
                            <div className="form-group__control">
                              <div className="reset-box">
                                <input
                                  className="form-control"
                                  type="text"
                                  {...register("site_name", {
                                    required: {
                                      value: true,
                                      message: replaceString(t("VALID_006"), [
                                        "現場名",
                                      ]),
                                    },
                                    maxLength: {
                                      value: validation.INPUT_MAX_LENGTH,
                                      message: replaceString(t("VALID_007"), [
                                        "現場名",
                                        "255",
                                      ]),
                                    },
                                  })}
                                  placeholder={t(
                                    "WEG_03_0010_placeholder_construction_name"
                                  )}
                                />
                              </div>
                              {errors.site_name && (
                                <p className="text-danger font-weight-bold">
                                  {errors.site_name.message}
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
                                    {...register("zip_code", {
                                      pattern: {
                                        value: validation.ZIP_CODE.PATTERN,
                                        message: t("VALID_004"),
                                      },
                                    })}
                                    placeholder={t(
                                      "WEG_03_0010_placeholder_post_code"
                                    )}
                                  />
                                </div>
                                <button className="btn btn-search-zip">
                                  {t("WEG_03_0010_text_btn_search_zip")}
                                </button>
                              </div>
                              <div className="form-check">
                                <input
                                  type="checkbox"
                                  id="checkSearchZip"
                                  name="check_search_zip"
                                  className="form-check-input"
                                  onClick={isCheckbox}
                                />
                                <label
                                  htmlFor="checkSearchZip"
                                  className="form-check-label"
                                >
                                  {t("WEG_03_0010_text_check_search_zip")}
                                </label>
                              </div>
                              {errors.zip_code && (
                                <p className="text-danger font-weight-bold">
                                  {errors.zip_code.message}
                                </p>
                              )}
                            </div>
                          </div>
                          <div className="d-block flex-wrap form-group">
                            <div className="form-group__label">
                              <label className="form-label">
                                {t("WEG_03_0010_label_prefectures")}
                              </label>
                              <span className="required">
                                {t("WEG_03_0010_required")}
                              </span>
                            </div>
                            <div className="form-group__control form-group__readonly">
                              <div className="reset-box">
                                <input
                                  className="form-control input-hidden-attribute"
                                  tabIndex="-1"
                                  type="text"
                                  {...register("site_prefectures", {
                                    required: replaceString(t("VALID_006"), [
                                      "都道府県",
                                    ]),
                                  })}
                                  placeholder={t(
                                    "WEG_03_0010_select_prefecture_default"
                                  )}
                                  disabled={isDisabled}
                                />
                              </div>
                              {errors.site_prefectures && (
                                <p className="text-danger font-weight-bold">
                                  {errors.site_prefectures.message}
                                </p>
                              )}
                            </div>
                          </div>
                          <div className="d-block flex-wrap form-group">
                            <div className="form-group__label">
                              <label className="form-label">
                                {t("WEG_01_0100_label_city")}
                              </label>
                              <span className="required">
                                {t("WEG_03_0010_required")}
                              </span>
                            </div>
                            <div className="form-group__control">
                              <div className="reset-box">
                                <input
                                  className="form-control"
                                  type="text"
                                  {...register("site_city", {
                                    required: replaceString(t("VALID_006"), [
                                      "市区町村",
                                    ]),
                                    maxLength: {
                                      value: validation.INPUT_MAX_LENGTH,
                                      message: replaceString(t("VALID_007"), [
                                        "市区町村",
                                        "255",
                                      ]),
                                    },
                                  })}
                                  placeholder={t(
                                    "WEG_03_0010_placeholder_city"
                                  )}
                                  disabled={isDisabled}
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
                              <span className="required">
                                {t("WEG_03_0010_required")}
                              </span>
                            </div>
                            <div className="form-group__control">
                              <div className="reset-box">
                                <textarea
                                  className="form-control form-address"
                                  rows="2"
                                  type="text"
                                  {...register("site_address", {
                                    required: replaceString(t("VALID_006"), [
                                      "番地（町名以降)",
                                    ]),
                                    maxLength: {
                                      value: validation.INPUT_MAX_LENGTH,
                                      message: replaceString(t("VALID_007"), [
                                        "番地（町名以降)",
                                        "255",
                                      ]),
                                    },
                                  })}
                                  placeholder={t(
                                    "WEG_03_0010_placeholder_address"
                                  )}
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
                      <div className="map">
                        <div className="d-block flex-wrap align-items-center form-group">
                          <div className="form-group__control mt-5">
                            <button className="btn btn-pink btn-sm" disabled>
                              {t("WEG_03_0010_btn_search_address")}
                            </button>
                            <p>
                              地図上のピンを操作して、工事現場の出入り口付近にピンを合わせてください。
                            </p>
                          </div>
                        </div>
                      </div>
                      <div className="text-center card-footer">
                        <button
                          className="btn-lg btn-cancel  btn btn-primary"
                          onClick={onClick}
                        >
                          <strong>{t("WEG_03_0010_btn_cancel")}</strong>
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <FooterNew />
    </>
  );
}
