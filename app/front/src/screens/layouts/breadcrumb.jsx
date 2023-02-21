import React from "react";
import { Trans } from "react-i18next";
import { Link, withRouter } from "react-router-dom";
import LinkName from "../../constants/link_name";

function Breadcrumb(props) {
  const { listBreadcrumb, customBreadcrumb } = props;

  return (
    <nav
      className={`breadcrumb-box ${
        props.match.path === LinkName.TOP ? "breadcrumb-box-mobile" : ""
      }`}
      aria-label="breadcrumb"
    >
      <ol className="breadcrumb">
        {listBreadcrumb ? (
          !customBreadcrumb ? (
            <>
              <li className="breadcrumb-item">
                <Link to={LinkName.TOP}>
                  <Trans i18nKey="WEG_03_0101_top" />
                </Link>
              </li>

              {listBreadcrumb.map((item, index) => {
                return item.link ? (
                  <li key={index} className="breadcrumb-item">
                    <Link to={item.link}>{item.name}</Link>
                  </li>
                ) : (
                  <li
                    key={index}
                    className="breadcrumb-item active"
                    aria-current="page"
                  >
                    {item.name}
                  </li>
                );
              })}
            </>
          ) : (
            listBreadcrumb.map((item, index) => {
              return item.handleClick ? (
                <li key={index} className="breadcrumb-item">
                  <span
                    className="breadcrumb-action"
                    onClick={item.handleClick}
                  >
                    {item.name}
                  </span>
                </li>
              ) : item.link ? (
                <li key={index} className="breadcrumb-item">
                  <Link to={item.link}>{item.name}</Link>
                </li>
              ) : (
                <li
                  key={index}
                  className="breadcrumb-item active"
                  aria-current="page"
                >
                  {item.name}
                </li>
              );
            })
          )
        ) : (
          <li className="breadcrumb-item active" aria-current="page">
            <Trans i18nKey="WEG_03_0101_top" />
          </li>
        )}
      </ol>
    </nav>
  );
}

export default withRouter(Breadcrumb);
