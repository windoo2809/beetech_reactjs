import React, { useState } from "react";
import { useTranslation } from "react-i18next";
import { Container, Pagination, Dropdown } from "react-bootstrap";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCircle, faCheckCircle } from "@fortawesome/free-solid-svg-icons";
import HeaderNew from "../layouts/header_new";
import FooterNew from "../layouts/footer_new";
import progress1 from "../../assets/images/icon/progress_1.svg";
import progress2 from "../../assets/images/icon/progress_2.svg";
import progress3 from "../../assets/images/icon/progress_3.svg";
import progress4 from "../../assets/images/icon/progress_4.svg";
import progress5 from "../../assets/images/icon/progress_5.svg";
import progress6 from "../../assets/images/icon/progress_6.svg";
import plus from "../../assets/images/icon/plus.png";
import minus from "../../assets/images/icon/minus.png";
import circle from "../../assets/images/icon/circle-solid.svg";
import yellow_circle from "../../assets/images/icon/yellow-circle.svg";
import "../../assets/scss/screens/list_construction.scss";
import ListConstructionInfoInput from "./components/form";

export default function ListConstructionInfo(props) {
  const [t] = useTranslation();
  const [isActive, setIsActive] = useState(false);
  const [isBtn, setIsBtn] = useState(false);
  const [paginations] = useState([
    {
      page: 1,
    },
    {
      page: 2,
    },
    {
      page: 3,
    },
  ]);
  const [pageSize, setPageSize] = useState(50);
  const [estimates, setEstimates] = useState([
    {
      type: 1,
      img: yellow_circle,
      imgCheck: true,
      active: true,
    },
    {
      type: 2,
      img: circle,
      imgCheck: true,
      active: false,
    },
    {
      type: 3,
      img: circle,
      imgCheck: true,
      active: false,
    },
    {
      type: 4,
      img: circle,
      imgCheck: true,
      active: false,
    },
    {
      type: 5,
      img: circle,
      imgCheck: true,
      active: false,
    },
    {
      type: 6,
      img: circle,
      imgCheck: true,
      active: false,
    },
  ]);
  const [iconEstimates, setIconEstimates] = useState([
    {
      type: 1,
      name: t("WEG_03_0002_route_map"),
      img: progress1,
      active: false,
    },
    {
      type: 2,
      name: t("WEG_03_0002_quotation"),
      img: progress2,
      active: false,
    },
    {
      type: 3,
      name: t("WEG_03_0002_purchase_order"),
      img: progress3,
      active: false,
    },
    {
      type: 4,
      name: t("WEG_03_0002_confirmed_quotation"),
      img: progress4,
      active: false,
    },
    {
      type: 5,
      name: t("WEG_03_0002_contract"),
      img: progress5,
      active: false,
    },
    {
      type: 6,
      name: t("WEG_03_0002_invoice"),
      img: progress6,
      active: false,
    },
  ]);
  const [labelEstimates, setLabelEstimates] = useState([
    {
      type: 1,
      name: t("WEG_03_0002_quotation_in_progress_accepted"),
      active: false,
    },
    {
      type: 2,
      name: t("WEG_03_0002_waiting_for_order"),
      active: false,
    },
    {
      type: 3,
      name: t("WEG_03_0002_order_processing"),
      active: false,
    },
    {
      type: 4,
      name: t("WEG_03_0002_ready_to_use"),
      active: false,
    },
    {
      type: 5,
      name: t("WEG_03_0002_waiting_for_contract"),
      active: false,
    },
    {
      type: 6,
      name: t("WEG_03_0002_under_contract"),
      active: false,
    },
  ]);

  const listPagination = () => {
    return (
      <>
        <Pagination>
          <Pagination.First />
          <Pagination.Prev />
          {paginations.map((item) => (
            <Pagination.Item key={item.page}>{item.page}</Pagination.Item>
          ))}
          <Pagination.Next />
          <Pagination.Last />
          <div className="page-total">
            <span>合{paginations.length}計</span>
          </div>
          <Dropdown>
            <Dropdown.Toggle id="dropdown-autoclose-true">
              {pageSize}
            </Dropdown.Toggle>
            <Dropdown.Menu>
              {[5, 10, 20, 25, 50, 100].map((pageSize) => (
                <Dropdown.Item key={pageSize}>{pageSize}</Dropdown.Item>
              ))}
            </Dropdown.Menu>
          </Dropdown>
        </Pagination>
      </>
    );
  };

  const listIconEstimate = () => {
    return iconEstimates.map((item) => {
      return (
        <button
          key={item.type}
          style={{ visibility: item.active === true ? "visible" : "hidden" }}
        >
          <img src={item.img} alt="" />
          <span>{item.name}</span>
        </button>
      );
    });
  };
  const listEstimate = () => {
    return estimates.map((item) => {
      return (
        <label key={item.type}>
          {item.active === true ? (
            <img src={yellow_circle} alt="" />
          ) : (
            <FontAwesomeIcon
              icon={item.imgCheck === true ? faCircle : faCheckCircle}
              onClick={() => handleClickEstimate(item.type)}
            />
          )}
          <hr className="line" />
        </label>
      );
    });
  };
  const listLabelEstimate = () => {
    return labelEstimates.map((item) => {
      return (
        <label
          key={item.type}
          style={{ visibility: item.active === true ? "visible" : "hidden" }}
        >
          {item.name}
        </label>
      );
    });
  };

  const handleClickEstimate = (type) => {
    const resultIconEstimate = iconEstimates.filter((item) => {
      if (item.type <= type) {
        return (item.active = true);
      }
      return {
        ...item,
        active: (item.active = false),
      };
    });
    const resulLabelEstimate = labelEstimates.filter((item) => {
      if (item.type === type) {
        return (item.active = true);
      }
      return {
        ...item,
        active: (item.active = false),
      };
    });
    const resultEstimates = estimates.filter((item) => {
      if (item.type === type) {
        return (item.active = true);
      }
      return {
        ...item,
        active: (item.active = false),
        imgCheck: (item.imgCheck = false),
      };
    });
    estimates.filter((item) => {
      if (item.type > type) {
        return (item.imgCheck = true);
      } else if (item.type === 6) {
        return setIsBtn(true);
      } else {
        return setIsBtn(false);
      }
    });

    setEstimates(resultEstimates);
    setIconEstimates(resultIconEstimate);
    setLabelEstimates(resulLabelEstimate);
  };

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer">
        <div className="page-template">
          <Container>
            <div className="wrapper">
              {listPagination()}
              <div className="template-list-construction">
                <ListConstructionInfoInput
                  setIsActive={setIsActive}
                  isActive={!isActive}
                  minus={minus}
                  plus={plus}
                  listIconEstimate={listIconEstimate}
                  listEstimate={listEstimate}
                  listLabelEstimate={listLabelEstimate}
                  isBtn={isBtn}
                />
              </div>
            </div>
          </Container>
        </div>
      </div>
      <FooterNew />
    </>
  );
}
