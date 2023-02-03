import React from 'react';
import LinkName from '../constants/link_name';
import { Route, Redirect, useLocation } from 'react-router-dom';
import _ from 'underscore';
import { clearLoginData, getUserFromAccessToken, checkPermissionForPage } from '../helpers/helpers';

function ConditionalRenderer({
    component: Component,
    propsIsSuper,
    propsIsManyBranch,
    routeProps,
}) {
    const location = useLocation();
    const localIsSuper = _.has(sessionStorage, 'isSuper');
    const localIsManyBranch = _.has(sessionStorage, 'isManyBranch');
    const localIsFirstLogin = _.has(sessionStorage, 'isFirstLogin');
    const userData = JSON.parse(sessionStorage.getItem('userData'));
    const localToken = sessionStorage.getItem('token');
    const hasAccessToken = (
        !_.isEmpty(userData) && 
        _.has(userData, 'access_token') && 
        userData.access_token &&
        String(userData.access_token).trim().length > 0
    );
    const loginInfo = getUserFromAccessToken();
    const localBackFirstLogin = _.has(sessionStorage, 'backFirstLogin');

    if ( localBackFirstLogin ) {
        clearLoginData();
        return (<Redirect to={LinkName.LOGIN}/>);
    }


    // check permission access to page
    if (hasAccessToken) {
        let isAccess = checkPermissionForPage(location.pathname, loginInfo.role);
        if (isAccess === false) {
            return (<Redirect to={{pathname: LinkName.TOP, state: { from: routeProps.location }}}/>);
        }
    }
    if (propsIsSuper) {
        if (hasAccessToken && localIsSuper) {
            return <Component {...routeProps} />;
        } else {
            clearLoginData();
            return (
                <Redirect
                    to={{
                        pathname: LinkName.LOGIN,
                        state: { from: routeProps.location }
                    }}
                />
            );
        }
    } 
    if (propsIsManyBranch) {
        if (hasAccessToken && localIsManyBranch) {
            return <Component {...routeProps} />;
        } else {
            clearLoginData();
            return (
                <Redirect
                    to={{
                        pathname: LinkName.LOGIN,
                        state: { from: routeProps.location }
                    }}
                />
            );
        }
    }

    if (hasAccessToken && !localIsFirstLogin && !localToken) {
        return <Component {...routeProps} />;
    }
    return (
        <Redirect
            to={{
                pathname: LinkName.LOGIN,
                state: { from: routeProps.location }
            }}
        />
    );
};

function ProtectedRoute({
    component: Component,
    isSuper = false,
    isManyBranch = false,
    ...rest
}) {
    return (
        <Route
            {...rest}
            render={(routeProps) => (
                <ConditionalRenderer
                    routeProps={routeProps}
                    propsIsSuper={isSuper}
                    propsIsManyBranch={isManyBranch}
                    component={Component}
                />
            )}
        />
    );
}

export default ProtectedRoute;