import React, {Component} from 'react';

import { Button, Modal, Transfer, Alert } from 'antd';

import { withTranslation } from 'react-i18next';

class MfwColVis extends Component {
    
    constructor(props) {
        super(props);
        var columns = [],
            visible = [];
        props.all.map(col => {
            columns.push({
                key: col.dataIndex,
                title: col.title
            });
        });
        this.state = {
            show: false,
            columns: columns,
            visible: [...this.props.visible]
        };
        this.setColumns = this.setColumns.bind(this);
        this.footer = this.footer.bind(this);
    }
    
    setColumns() {
        this.setState({show: false});
        this.props.setColVis(this.state.visible);
    }
    
    footer(props, { direction }) {
        if (direction === 'left') {
            return <Alert message={this.props.t('grid.column.hidden')} type="info" />;
        }
        if (direction === 'right') {
            return <Alert message={this.props.t('grid.column.visible')} type="info" />;
        }
    }
    
    render() {
        return (
            <React.Fragment>
                <Button onClick={() => this.setState({show: true})}>!!!</Button>
                {this.state.show ? <Modal
                      title={this.props.t('grid.column.hide_view')}
                      visible={true}
                      closable={true}
                      width={1000}
                      okText={this.props.t('common.apply')}
                      cancelText={this.props.t('common.cancel')}
                      onCancel={() => {this.setState({show: false})}}
                      onOk={this.setColumns}>
                    <Transfer
                      dataSource={this.state.columns}
                      targetKeys={this.state.visible}
                      render={item => item.title}
                      listStyle={{
                        width: '100%',
                        height: 300
                      }}
                      onChange={(nextTargetKeys) => this.setState({visible: nextTargetKeys})}
                      footer={this.footer}
                    />
                    </Modal>
                    : null}
            </React.Fragment>
        )
    }
}

export default withTranslation()(MfwColVis);