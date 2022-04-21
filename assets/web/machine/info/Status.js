import React, {Component} from 'react';

import { Descriptions, Row, Col, Typography, Space } from 'antd';
import { withTranslation } from 'react-i18next';
import { faLink, faLockOpen, faLock } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';


class Status extends Component {
    render() {
        const cols = 2+(this.props.info.egm_status.PLAYER_ID ? 1 : 0)+(this.props.info.status.IOC_STATUS == 1 ? 1 : 0);
        return (<Row>
            <Col span={24/cols}>
                <Descriptions bordered column="1">
                    <Descriptions.Item label={this.props.t('machine.egm._egm')}>
                        <Space>
                            {this.props.info.status.EGM_CONNECT == 0 ? <Typography.Text disabled>
                                <FontAwesomeIcon icon={faLink} title={this.props.t('common.disconnected')}/>
                            </Typography.Text> : <Typography.Text>
                                <FontAwesomeIcon icon={faLink} title={this.props.t('common.connected')}/>
                            </Typography.Text>}
                            <FontAwesomeIcon {...(this.props.info.status.EGM_LOCK == 0 ? {icon: faLockOpen, title: this.props.t('common.unlocked')} : 
                               {icon: faLockOpen, title: this.props.t('common.locked')})}/>
                        </Space>
                    </Descriptions.Item>
                </Descriptions>
            </Col>
        </Row>);
    }
}

export default withTranslation()(Status);