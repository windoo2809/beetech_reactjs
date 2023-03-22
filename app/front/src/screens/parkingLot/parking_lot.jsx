import React, { useEffect, useState } from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { FormProvider, useForm } from "react-hook-form";
import { useHistory } from "react-router-dom";
import { replaceString } from "../../helpers/helpers";
import HeaderNew from "../layouts/header_new";
import FooterNew from "../layouts/footer_new";
import LinkName from "../../constants/link_name";
import "../../assets/scss/screens/parking_lot.scss";
import ParkingLotInput from "./components/form";

export default function FormParkingLot() {
  const [t] = useTranslation();
  const [total, setTotal] = useState(0);
  const history = useHistory();

  const methods = useForm({
    defaultValues: {
      wagonCar: 0,
      amuontLightTruck: 0,
      twoTTruck: 0,
      amountDiff: 0,
      startDate: "",
      endDate: "",
      requestDetails: "1"
    },
    shouldUseNativeValidation: true,
    mode: "onChange",
    reValidateMode: "onChange",
  });

  const { handleSubmit, watch } = methods;

  const { wagonCar, amuontLightTruck, twoTTruck, amountDiff } = watch();

  useEffect(() => {
    setTotal(
      Number(wagonCar) +
        Number(amuontLightTruck) +
        Number(twoTTruck) +
        Number(amountDiff)
    );
  }, [wagonCar, amuontLightTruck, twoTTruck, amountDiff]);

  const handleSubmitForm = (data) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const fiveYearsLater = new Date(today.getFullYear() + 5, 0, 1);
    const checkStartDate = new Date(data.startDate);
    const checkEndDate = new Date(data.endDate);
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
                  <FormProvider {...methods}>
                    <form onSubmit={handleSubmit(handleSubmitForm)}>
                      <ParkingLotInput total={total} />

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
