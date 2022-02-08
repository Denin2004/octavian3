import React, {Component} from 'react';

import { Button, Modal } from 'antd';

import { withTranslation } from 'react-i18next';

class MfwColVis extends Component {
    
    constructor(props) {
        super(props);
        this.state = {
            show: false
        };
        this.setColumns = this.setColumns.bind(this);
    }
    
    setColumns() {
        this.setState({show: false});
    }
    
    render() {
        console.log(this.props);
        return (
            <React.Fragment>
                <Button onClick={() => this.setState({show: true})}>!!!</Button>
                {this.state.show ? <Modal
                      title={this.props.t('grid.hide_view_columns')}
                      visible={true}
                      closable={true}
                      okText={this.props.t('common.apply')}
                      cancelText={this.props.t('common.cancel')}
                      onCancel={() => {this.setState({show: false})}}
                      onOk={this.setColumns}>
                    </Modal>
                    : null}
            </React.Fragment>
        )
    }
}

export default withTranslation()(MfwColVis);