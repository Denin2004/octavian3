import React, {Component} from 'react';
import {Route, Switch, Redirect, Link, withRouter, generatePath} from 'react-router-dom';

import { Layout, Menu, Spin, message, Modal } from 'antd';

import { faGem, faCrown, faMoneyBillAlt, faUsers, faTv, faTools, faBug } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import { withTranslation } from 'react-i18next';

import axios from 'axios';

import Report from '@app/web/report/Main';
import MachineInfo from '@app/web/machine/info/Main';

class Main extends Component {
    constructor(props){
        super(props);
        this.state = {
            loading: true,
            machInfo: null
        };
        this.showMachine = this.showMachine.bind(this);
    }

    componentDidMount() {
        axios.get(
            '/config',
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            if (res.data.success) {
                window.mfwApp.urls = JSON.parse(res.data.urls);
                window.mfwApp.user = res.data.user;
                this.setState({
                    loading: false,
                    userName: res.data.user.name,
                    userID: res.data.user.id
                });
            } else {
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            if (error.response && error.response.data) {
                message.error(this.props.t(error.response.data.error));
            } else {
                message.error(error.toString());
            }
        });
    }
    
    showMachine() {
        axios.get(
            '/machines/info/1/777777787',
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            if (res.data.success) {
                this.setState({
                    machInfo: res.data.data[0].machineInfo
                });
            } else {
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            if (error.response && error.response.data) {
                message.error(this.props.t(error.response.data.error));
            } else {
                message.error(error.toString());
            }
        });
    }

    render() {
        return (
            <Layout style={{ minHeight: '100vh' }}>
                <Layout.Sider collapsed={true} collapsible>
                    <Menu mode="inline" theme="dark" className="mfw-main-menu">
                        <Menu.Item key="1" icon={<div><FontAwesomeIcon icon={faGem}/><div className="ant-menu-title-content">Mashines</div></div>}/>
                        <Menu.Item key="2" icon={<div><FontAwesomeIcon icon={faCrown}/><div className="ant-menu-title-content">Jackpots</div></div>}/>
                        <Menu.Item key="3" icon={<div><FontAwesomeIcon icon={faMoneyBillAlt}/><div className="ant-menu-title-content">Cage</div></div>}/>
                        <Menu.Item key="4" icon={<div><FontAwesomeIcon icon={faUsers}/><div className="ant-menu-title-content">Players</div></div>}/>
                        <Menu.Item key="5" icon={<div><FontAwesomeIcon icon={faTv}/><div className="ant-menu-title-content">Floor monitor</div></div>}/>
                        <Menu.SubMenu key="sub1" popupClassName="mfw-main-submenu" icon={<div><FontAwesomeIcon icon={faTools}/><div className="ant-menu-title-content">Admin</div></div>}>
                            <Menu.Item key="6">Option 5</Menu.Item>
                            <Menu.Item key="7">Option 6</Menu.Item>
                            <Menu.Item key="8">Option 7</Menu.Item>
                            <Menu.Item key="9">Option 8</Menu.Item>
                        </Menu.SubMenu>
                        <Menu.Item key="13" icon={<FontAwesomeIcon icon={faBug}/>} onClick={this.showMachine}/>                        
                        <Menu.Item key="10" icon={<Link to={generatePath('/report/page/:id', {id: 47})}><FontAwesomeIcon icon={faBug}/><div className="ant-menu-title-content">Test report</div></Link>}/>
                        <Menu.Item key="11" icon={<Link to={generatePath('/report/page/:id', {id: 97})}><FontAwesomeIcon icon={faBug}/><div className="ant-menu-title-content">Test report</div></Link>}/>
                        <Menu.Item key="12" icon={<Link to={generatePath('/report/page/:id', {id: 115})}><FontAwesomeIcon icon={faBug}/><div className="ant-menu-title-content">Test report</div></Link>}/>
                    </Menu>
                </Layout.Sider>
                <Layout>
                    {this.state.loading ?
                    <Layout.Content>
                        <Spin/>
                    </Layout.Content> :
                    <Switch>
                        <Route path="/report/page/:id(\d+)" component={Report} />
                    </Switch>
                    }
                </Layout>
                { this.state.machInfo != null ? <Modal 
                        visible={true} 
                        closable={true}
                        footer={null}
                        onCancel={() => this.setState({machInfo: null})}
                        width="90%">
                        <MachineInfo info={this.state.machInfo}/>
                    </Modal> : null
                }
            </Layout>
        )
    }
}

export default withRouter(withTranslation()(Main));