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
  const [posts, setPosts] = useState([]);

  const {
    register,
    handleSubmit,
    reset,
    getValues,
    formState: { errors },
  } = useForm({
    shouldUseNativeValidation: true,
  });

  // const onSubmit = (data) => {
  //   customer
  //   .getCustomer()
  //   .then((data) => {
  //     console.log(data);
  //   });
  // };

  useEffect(() => {
    customer
      .getCustomer()
      .then((data) => {
        console.log(data);
        setPosts(data);
      })
      .catch((err) => {
        console.log(err.message);
      });
  }, []);

  const onClick = () => {
    history.push("/");
  };
  return (
    <>
      <HeaderNew />
      <div className="page-template">
        <Container>
          <div className="template-form-customer">
            <div className="wrap">
              <h1 className="title-breadcrumb font-weight-bold">
                {t("WEG_03_0010_sub_title_page")}
              </h1>
              <div className="form-customer">
                <form onSubmit={handleSubmit()}>
                  <div className="group-form mb-5">
                    <div className="form-group">
                      <label>
                        {t("WEG_03_0010_label_construction_number")}
                      </label>
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
                      {errors.construction_number && (
                        <p className="text-error d-block error-active">
                          {errors.construction_number.message}
                        </p>
                      )}
                    </div>
                    <div className="form-group">
                      <label>{t("WEG_03_0010_label_construction_name")}</label>
                      <input
                        className="form-control"
                        type="text"
                        {...register("site_name", {
                          required: {
                            value: true,
                            message: replaceString(t("VALID_006"), ["現場名"]),
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
                      {errors.site_name && (
                        <p className="text-error d-block error-active">
                          {errors.site_name.message}
                        </p>
                      )}
                    </div>
                    <div className="form-group">
                      <label>{t("WEG_03_0010_label_post_code")}</label>
                      <div className="row">
                        <div className="col-sm-9">
                          <input
                            className="form-control"
                            type="text"
                            {...register("zip_code", {
                              pattern: {
                                value: validation.ZIP_CODE.PATTERN,
                                message: t("VALID_004"),
                              },
                            })}
                            placeholder={t("WEG_03_0010_placeholder_post_code")}
                          />
                        </div>
                        <div className="col-sm-3 action-box">
                          <button className="btn btn-dark">
                            {t("WEG_03_0010_text_btn_search_zip")}
                          </button>
                        </div>
                      </div>

                      {errors.zip_code && (
                        <p className="text-error d-block error-active">
                          {errors.zip_code.message}
                        </p>
                      )}
                    </div>
                    <div className="form-group">
                      <div className="form-group__check">
                        <div className="form-check">
                          <input
                            id="option-1"
                            className="form-check-input"
                            type="checkbox"
                            {...register("search_zip")}
                            value="1"
                          />
                          <label
                            htmlFor="option-1"
                            className="form-check-label"
                          >
                            {t("WEG_03_0010_text_check_search_zip")}
                          </label>
                        </div>
                      </div>
                    </div>

                    <div className="form-group">
                      <label>{t("WEG_03_0010_label_prefectures")}</label>
                      <input
                        className="form-control"
                        type="text"
                        {...register("site_prefectures", {
                          required: replaceString(t("VALID_006"), ["都道府県"]),
                        })}
                        placeholder={t("")}
                      />
                      {errors.site_prefectures && (
                        <p className="text-error d-block error-active">
                          {errors.site_prefectures.message}
                        </p>
                      )}
                    </div>

                    <div className="form-group">
                      <label>{t("WEG_01_0100_label_city")}</label>
                      <input
                        className="form-control"
                        type="text"
                        {...register("site_city", {
                          required: replaceString(t("VALID_006"), "市区町村"),
                        })}
                        placeholder={t("WEG_03_0010_placeholder_city")}
                      />
                      {errors.site_city && (
                        <p className="text-error d-block error-active">
                          {errors.site_city.message}
                        </p>
                      )}
                    </div>

                    <div className="form-group">
                      <label>{t("WEG_03_0010_label_address")}</label>
                      <input
                        className="form-control"
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
                        placeholder={t("WEG_03_0010_placeholder_address")}
                      />
                      {errors.site_address && (
                        <p className="text-error d-block error-active">
                          {errors.site_address.message}
                        </p>
                      )}
                    </div>
                    <div className="action-box">
                      <button type="submit" className="btn btn-primary" onClick={onClick}>
                        {t("WEG_03_0010_btn_cancel")}
                      </button>
                    </div>
                  </div>
                  <div className="action-box">
                    <button className="btn btn-secondary" onClick={onClick}>
                      {t("WEG_03_0010_btn_cancel")}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </Container>
      </div>
      <FooterNew />
    </>
  );
}
