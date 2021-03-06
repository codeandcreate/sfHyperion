/*
  Copyright (C) 2013 Jolla Ltd.
  Contact: Thomas Perl <thomas.perl@jollamobile.com>
  All rights reserved.

  You may use this file under the terms of BSD license as follows:

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the Jolla Ltd nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS OR CONTRIBUTORS BE LIABLE FOR
  ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

import QtQuick 2.0
import Sailfish.Silica 1.0
import "./js/request.js" as BackendRequest

Page {
    id: page


    property bool isColorWheel: false
    property bool isOn: false
    property string previewColor: "#000000"

    // The effective value will be restricted by ApplicationWindow.allowedOrientations
    allowedOrientations: Orientation.All

    // To enable PullDownMenu, place our content in a SilicaFlickable
    SilicaFlickable {
        anchors.fill: parent

        // PullDownMenu and PushUpMenu must be declared in SilicaFlickable, SilicaListView or SilicaGridView
        PullDownMenu {
            MenuItem {
                text: qsTr("Settings")
                onClicked: pageStack.push(Qt.resolvedUrl("Settings.qml"))
            }
            MenuItem {
                text: qsTr("test")
                onClicked: BackendRequest.postToBackend("on")
            }
        }

        contentHeight: column.height

        Column {
            id: column

            width: page.width
            spacing: Theme.paddingLarge
            PageHeader {
                title: qsTr("sfHyperion")
            }

            Button {
                id: switchButton
                anchors.horizontalCenter: parent.horizontalCenter
                width: 300
                text: "Ambi On/Off"
                onClicked: {
                     if (isOn) {
                         BackendRequest.postToBackend("off");
                         isOn = false;
                     } else {
                         BackendRequest.postToBackend("on");
                         isOn = true;
                     }
                }
            }

            Rectangle {
                width: parent.width
                height: editColorRow.height + width*(3/4)
                color: "transparent"

                Row {
                    id: editColorRow
                    width: parent.width
                    TextSwitch {
                        id: tsColorWheel
                        anchors.verticalCenter: parent.verticalCenter
                        text: qsTr("Edit color %1").arg(previewColor)
                        automaticCheck: false
                        checked: isColorWheel
                        width: parent.width*(3/4)-Theme.paddingLarge
                        enabled: currentColor < colors.length
                        onClicked: {
                            isColorWheel = !isColorWheel
                            if (isColorWheel) {
                                previewColor = colors[currentColor]
                                colorWheelRect.color = colors[currentColor]
                            } else {
                                colorSelectorRepeater.model = colors
                                colorCursor.visible = false
                            }
                        }
                    }

                    Rectangle {
                        id: colorWheelRect
                        anchors.verticalCenter: parent.verticalCenter
                        visible: isColorWheel
                        width: parent.width*(1/4)
                        height: parent.height - Theme.paddingLarge
                        radius: 5
                    }
                }

                Grid {
                    anchors.top: editColorRow.bottom
                    id: colorSelector
                    visible: !isColorWheel
                    columns: 4
                    Repeater {
                        id: colorSelectorRepeater
                        model: colors
                        Rectangle {
                            width: col.width/colorSelector.columns
                            height: col.width/colorSelector.columns
                            radius: 10
                            color: (index == currentColor) ? colors[index] : "transparent"
                            Rectangle {
                                width: parent.width - 20
                                height: parent.height - 20
                                radius: 5
                                color: colors[index]
                                anchors.centerIn: parent
                            }
                            BackgroundItem {
                                anchors.fill: parent
                                onClicked: {
                                    currentColor = index
                                    previewColor = colors[currentColor]
                                }
                            }
                        }
                    }
                }
                Rectangle {
                    id: colorWheelPlaceHolder
                    visible: isColorWheel
                    anchors.top: editColorRow.bottom
                    color: "transparent"
                    height: parent.width*(3/4)
                    width: parent.width
                    radius: 10
                    anchors.horizontalCenter: parent.horizontalCenter

                    Rectangle {
                        id: colorCursor
                        visible: false
                        x: 50
                        y: 50
                        width: 10
                        height: 10
                        z: 2
                        color: "transparent"
                        border.color: "White"
                        border.width: 2
                        radius: width*0.5
                    }
                    Canvas {
                        id: colorWheelCanvas
                        anchors.fill: parent
                        antialiasing: true
                        renderStrategy: Canvas.Immediate
                        onPaint: {
                            var ctx = getContext('2d')
                            var border = 20
                            ctx.clearRect(0, 0, width, height);
                            /* RGB gradient */
                            var grd = ctx.createLinearGradient(0, 0, width-2*border, 0);
                            grd.addColorStop(0, "red");
                            grd.addColorStop(1/6, "magenta");
                            grd.addColorStop(2/6, "blue");
                            grd.addColorStop(3/6, "cyan");
                            grd.addColorStop(4/6, "lime");
                            grd.addColorStop(5/6, "yellow");
                            grd.addColorStop(1, "red");
                            ctx.fillStyle = grd;
                            ctx.fillRect(0, 0, width-2*border, height);
                            /* Brightness gradient on top of RGB gradient */
                            var grd2 = ctx.createLinearGradient(0,0,0,height);
                            grd2.addColorStop(0, "white");
                            grd2.addColorStop(2/5, "transparent");
                            grd2.addColorStop(3/5, "transparent");
                            grd2.addColorStop(1, "black");
                            ctx.fillStyle = grd2;
                            ctx.fillRect(0, 0, width-2*border, height);
                            /* Grey-scale gradient at right edge */
                            var grd3 = ctx.createLinearGradient(0,0,0,height);
                            grd3.addColorStop(0, "white");
                            grd3.addColorStop(border/height, "white")
                            grd3.addColorStop((height-border)/height, "black")
                            grd3.addColorStop(1, "black");
                            ctx.fillStyle = grd3;
                            ctx.fillRect(width-2*border, 0, 2*border, height);
                        }
                    }
                    MouseArea  {
                        id: area
                        anchors.fill: colorWheelCanvas

                        function rgbToHex(r, g, b) {
                            if (r > 255 || g > 255 || b > 255)
                                throw "Invalid color component"
                            return ((r << 16) | (g << 8) | b).toString(16)
                        }
                        function rgbToHexInverse(r, g, b) {
                            if (r > 255 || g > 255 || b > 255)
                                throw "Invalid color component"
                            return (((255-r) << 16) | ((255-g) << 8) | (255-b)).toString(16)
                        }

                        preventStealing: true
                        onPositionChanged: getColorAtPosition()

                        onPressed: {
                            colorCursor.visible = true
                            getColorAtPosition()
                        }

                        function getColorAtPosition() {
                            if (mouseX > 0 && mouseY > 0 && mouseX < width-1 && mouseY < height-1) {
                                var ctx = colorWheelCanvas.getContext('2d')
                                var p = ctx.getImageData(mouseX, mouseY, 1, 1).data;
                                var hex = "#" + ("000000" + rgbToHex(p[0], p[1], p[2])).slice(-6)
                                console.log("color at " + mouseX + "x" + mouseY + " is " + hex)
                                /* Just set them all ... */
                                colorWheelRect.color = hex
                                colors[currentColor] = hex
                                previewColor = hex
                                colorCursor.x = (mouseX) - 5
                                colorCursor.y = (mouseY) - 5
                                colorCursor.border.color = "#" + ("000000" + rgbToHexInverse(p[0], p[1], p[2])).slice(-6)
                            }
                        }
                    }
                }
            }

            Slider {
                label: "Brightness"
                value: 100
                minimumValue:1
                maximumValue:100
                stepSize: 1
                width: parent.width
                valueText: value.toFixed(2)
            }
        }
    }
}

