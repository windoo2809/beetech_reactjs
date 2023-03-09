import React, { useEffect, useState } from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { useForm } from "react-hook-form";
import { useHistory } from "react-router-dom";
import { replaceString } from "../helpers/helpers";
import HeaderNew from "./layouts/header_new";
import FooterNew from "./layouts/footer_new";
import LinkName from "../constants/link_name";
import validation from "../constants/validation";
import "../assets/scss/screens/parking_lot.scss";

export default function ParkingLot() {
  const [t] = useTranslation();
  const [total, setTotal] = useState(0);
  const history = useHistory();

  const {
    register,
    handleSubmit,
    watch,
    formState: { errors },
  } = useForm({
    defaultValues: {
      wagonCar: 0,
      amuontLightTruck: 0,
      twoTTruck: 0,
      amountDiff: 0,
      startDate: "",
      endDate: "",
    },
    shouldUseNativeValidation: true,
    mode: "onChange",
    reValidateMode: "onChange",
  });

  const {
    wagonCar,
    amuontLightTruck,
    twoTTruck,
    amountDiff,
    startDate,
    endDate,
  } = watch();

  useEffect(() => {
    setTotal(
      Number(wagonCar) +
        Number(amuontLightTruck) +
        Number(twoTTruck) +
        Number(amountDiff)
    );
  }, [wagonCar, amuontLightTruck, twoTTruck, amountDiff]);

  const onSubmit = () => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const fiveYearsLater = new Date(today.getFullYear() + 5, 0, 1);
    const checkStartDate = new Date(startDate);
    const checkEndDate = new Date(endDate);
    if (total === 0) {
      alert(t("CMN0013-I"));
    }
    if (checkEndDate < checkStartDate) {
      alert(
        replaceString(t("CLT0008-I"), [
          t("WEG_03_0101_endDate"),
          t("WEG_03_0101_startDate"),
        ])
      );
    } else if (checkStartDate < today) {
      alert(replaceString(t("CMN0017-I"), [t("WEG_03_0101_startDate"), "5年"]));
    } else if (checkEndDate > fiveYearsLater) {
      alert(replaceString(t("CMN0017-I"), [t("WEG_03_0101_endDate"), "5年"]));
    }
  };

  const onClick = () => {
    history.push(LinkName.CONSTRUCTION_INFO);
  };

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer">
        <div className="page-template">
          <Container>
            <div className="template-parking-lot">
              <div className="wrap">
                <div className="form-parking-lot">
                  <form onSubmit={handleSubmit(onSubmit)}>
                    <div className="group-form mb-5">
                      <div className="form-group__check">
                        <div className="form-check">
                          <input
                            id="option-1"
                            className="form-check-input"
                            type="radio"
                            value="1"
                            name="radio"
                            defaultChecked
                          />
                          <label
                            htmlFor="option-1"
                            className="form-check-label"
                          >
                            {t("WEG_03_0101_selectRequestTypeTitle")}
                          </label>
                        </div>
                        <div className="form-check">
                          <input
                            id="option-2"
                            className="form-check-input"
                            type="radio"
                            name="radio"
                            value="2"
                          />
                          <label
                            htmlFor="option-2"
                            className="form-check-label"
                          >
                            {t("WEG_03_0101_requestDetailsBookParking")}
                          </label>
                        </div>
                      </div>
                      <div className="form-group">
                        <label>
                          {t("WEG_03_0101_startDate")}
                          <span>{t("WEG_03_0101_required")}</span>
                        </label>
                        <input
                          {...register("startDate", {
                            required: t("VALID_022"),
                            pattern: {
                              value: validation.DATE.PATTERN,
                              message: t("VALID_024"),
                            },
                          })}
                          type="date"
                          className="form-control"
                          placeholder={t("WEG_03_0101_enterStartDate")}
                        />
                      </div>
                      {errors.startDate && (
                        <p className="text-error d-block error-active">
                          {errors.startDate.message}
                        </p>
                      )}
                      <div className="form-group">
                        <label>
                          {t("WEG_03_0101_endDate")}
                          <span>{t("WEG_03_0101_required")}</span>
                        </label>
                        <input
                          type="date"
                          className="form-control"
                          {...register("endDate", {
                            required: t("VALID_022"),
                            pattern: {
                              value: validation.DATE.PATTERN,
                              message: t("VALID_024"),
                            },
                          })}
                          placeholder={t("WEG_03_0101_enterEndDate")}
                        />
                      </div>
                      {errors.endDate && (
                        <p className="text-error d-block error-active">
                          {errors.endDate.message}
                        </p>
                      )}
                      <div className="group-number">
                        <div className="title">
                          {t("WEG_03_0101_amountCoach")}
                          <span>{t("WEG_03_0101_required")}</span>
                        </div>
                        <div className="form-group">
                          <label>{t("WEG_03_0101_wagonCar")}</label>
                          <input
                            {...register("wagonCar", {
                              required: t("VALID_035"),
                              max: {
                                value: validation.PARKING_LOT.WAGON_CAR.MAX,
                                message: replaceString(t("VALID_036"), [
                                  t("WEG_03_0101_wagonCar"),
                                  "1000",
                                ]),
                              },
                              min: {
                                value: validation.PARKING_LOT.WAGON_CAR.MIN,
                                message: replaceString(t("VALID_037"), [
                                  t("WEG_03_0101_wagonCar"),
                                  0,
                                ]),
                              },
                            })}
                            type="number"
                            className="form-control"
                          />
                          <span>{t("WEG_03_0101_stand")}</span>
                          <input type="checkbox" className="form-check" />
                        </div>
                        {errors.wagonCar && (
                          <p className="text-error d-block error-active">
                            {errors.wagonCar.message}
                          </p>
                        )}
                        <div className="form-group">
                          <label>{t("WEG_03_0101_amuontLightTruck")}</label>
                          <input
                            {...register("amuontLightTruck", {
                              required: t("VALID_035"),
                              max: {
                                value:
                                  validation.PARKING_LOT.AMOUNT_LIGHT_TRUCK.MAX,
                                message: replaceString(t("VALID_036"), [
                                  t("WEG_03_0101_amuontLightTruck"),
                                  "1000",
                                ]),
                              },
                              min: {
                                value:
                                  validation.PARKING_LOT.AMOUNT_LIGHT_TRUCK.MIN,
                                message: replaceString(t("VALID_037"), [
                                  t("WEG_03_0101_amuontLightTruck"),
                                  "0",
                                ]),
                              },
                            })}
                            type="number"
                            className="form-control"
                          />
                          <span>{t("WEG_03_0101_stand")}</span>
                          <input type="checkbox" className="form-check" />
                        </div>
                        {errors.amuontLightTruck && (
                          <p className="text-error d-block error-active">
                            {errors.amuontLightTruck.message}
                          </p>
                        )}
                        <div className="form-group">
                          <label>{t("WEG_03_0101_2tTruck")}</label>
                          <input
                            {...register("twoTTruck", {
                              required: t("VALID_035"),
                              max: {
                                value: validation.PARKING_LOT.TWO_T_TRUCK.MAX,
                                message: replaceString(t("VALID_036"), [
                                  t("WEG_03_0101_2tTruck"),
                                  "1000",
                                ]),
                              },
                              min: {
                                value: validation.PARKING_LOT.TWO_T_TRUCK.MIN,
                                message: replaceString(t("VALID_037"), [
                                  t("WEG_03_0101_2tTruck"),
                                  "0",
                                ]),
                              },
                            })}
                            className="form-control"
                            type="number"
                          />
                          <span>{t("WEG_03_0101_stand")}</span>
                          <input type="checkbox" className="form-check" />
                        </div>
                        {errors.twoTTruck && (
                          <p className="text-error d-block error-active">
                            {errors.twoTTruck.message}
                          </p>
                        )}
                        <div className="form-group">
                          <label>{t("WEG_03_0101_amountDiff")}</label>
                          <input
                            {...register("amountDiff", {
                              required: t("VALID_035"),
                              max: {
                                value: validation.PARKING_LOT.AMOUNT_DIFF.MAX,
                                message: replaceString(t("VALID_036"), [
                                  t("WEG_03_0101_amountDiff"),
                                  "1000",
                                ]),
                              },
                              min: {
                                value: validation.PARKING_LOT.AMOUNT_DIFF.MIN,
                                message: replaceString(t("VALID_037"), [
                                  t("WEG_03_0101_amountDiff"),
                                  "0",
                                ]),
                              },
                            })}
                            className="form-control"
                            type="number"
                          />
                          <span>{t("WEG_03_0101_stand")}</span>
                          <input type="checkbox" className="form-check" />
                        </div>
                        {errors.amountDiff && (
                          <p className="text-error d-block error-active">
                            {errors.amountDiff.message}
                          </p>
                        )}
                      </div>
                      <div className="form-group">
                        <label>{t("WEG_03_0101_otherDetails")}</label>
                        <textarea
                          className="form-control"
                          {...register("otherDetails", {
                            maxLength: {
                              value:
                                validation.PARKING_LOT.OTHER_DETAILS.MAX_LENGTH,
                              message: replaceString(t("VALID_007"), [
                                t("WEG_03_0101_otherDetails"),
                                "20,000",
                              ]),
                            },
                          })}
                          placeholder={t("WEG_03_0101_enterOtherDetails")}
                        ></textarea>
                      </div>
                      {errors.otherDetails && (
                        <p className="text-error d-block error-active">
                          {errors.otherDetails.message}
                        </p>
                      )}
                      <div className="total-car">
                        <div className="form-group">
                          <label>{t("WEG_03_0101_totalNumberUsers")}</label>
                          <span>{total}</span>
                          <p>{t("WEG_03_0101_stand")}</p>
                        </div>
                      </div>
                      <div className="form-group">
                        <label>{t("WEG_03_0101_hope")}</label>
                        <input
                          type="text"
                          className="form-control"
                          {...register("hope", {
                            maxLength: {
                              value: validation.PARKING_LOT.HOPE.MAX_LENGTH,
                              message: replaceString(t("VALID_007"), [
                                t("WEG_03_0101_hope"),
                                "20,000",
                              ]),
                            },
                          })}
                          placeholder={t("WEG_03_0101_enterHope")}
                        />
                      </div>
                      {errors.hope && (
                        <p className="text-error d-block error-active">
                          {errors.hope.message}
                        </p>
                      )}
                      <div className="form-group">
                        <label>{t("WEG_03_0101_mailCC")}</label>
                        <input
                          type="text"
                          className="form-control"
                          {...register("mailCC", {
                            maxLength: {
                              value: validation.PARKING_LOT.MAIL_CC.MAX_LENGTH,
                              message: replaceString(t("VALID_049"), "2048"),
                            },
                            pattern: {
                              value: validation.PARKING_LOT.MAIL_CC.PATTERN,
                              message: t("VALID_017"),
                            },
                          })}
                          placeholder={t("WEG_03_0101_enterMailCC")}
                        />
                      </div>
                      {errors.mailCC && (
                        <p className="text-error d-block error-active">
                          {errors.mailCC.message}
                        </p>
                      )}
                    </div>
                    <div className="action-box">
                      <button
                        type="submit"
                        className="btn btn-secondary back-button"
                        onClick={onClick}
                      >
                        {t("WEG_03_0101_returnConstructionInfo")}
                      </button>
                      <button
                        type="submit"
                        className="btn btn-primary next-button"
                      >
                        {t("WEG_03_0101_next")}
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
