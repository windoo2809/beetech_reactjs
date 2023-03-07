import React, { lazy, Suspense, useState } from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import ProtectedRoute from "./auth/protected_route";
import PageLoader from "./components/page_loader";
import LinkName from "./constants/link_name";
import AppContext from "./context/appContext";
const Login = lazy(() => import("./screens/login.jsx"));
const FormMail = lazy(() => import("./screens/form_mail.jsx"));
const StaticNotFound = lazy(() => import("./screens/notfound"));
const ConstructionInfo = lazy(() => import("./screens/construction_info.jsx"));
const DetailConstructionInfo = lazy(() => import("./screens/detail_construction_info.jsx"));
const Dashboard = lazy(() => import("./screens/dashboard.jsx"));
const ParkingLot = lazy(() => import("./screens/parking_lot.jsx"));

localStorage.setItem("i18nextLng", "jp");

function App() {
  const [appState, setAppState] = useState({
    loginInfo: null,
  });

  const setAppStateByKey = (obj) => {
    let dataTmp = { ...appState };
    Object.entries(obj).forEach(([key, value]) => {
      dataTmp[key] = value;
    });

    setAppState(dataTmp);
  };

  const appStateInfo = {
    appState,
    setAppStateByKey,
  };

  return (
    <BrowserRouter>
      <AppContext.Provider value={appStateInfo}>
        <Suspense fallback={<PageLoader />}>
          <Switch>
            <Route exact path={LinkName.PAGE_LOGIN} component={Login} />
            <Route exact path={LinkName.LOGIN} component={Login} />
            <Route exact path={LinkName.MAIL_FORM} component={FormMail} />
            <Route exact path={LinkName.CONSTRUCTION_INFO} component={ConstructionInfo} />
            <Route exact path={LinkName.PARKING_LOT} component={ParkingLot} />
            <Route exact path={LinkName.DASHBOARD} component={Dashboard} />
            <ProtectedRoute component={StaticNotFound} />
          </Switch>
        </Suspense>
      </AppContext.Provider>
    </BrowserRouter>
  );
}

export default App;
