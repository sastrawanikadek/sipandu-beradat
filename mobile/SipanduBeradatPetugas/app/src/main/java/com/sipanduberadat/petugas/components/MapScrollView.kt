package com.sipanduberadat.petugas.components

import android.annotation.SuppressLint
import android.content.Context
import android.util.AttributeSet
import android.view.MotionEvent
import android.widget.ScrollView

class MapScrollView(context: Context, attr: AttributeSet? = null): ScrollView(context, attr) {
    override fun onInterceptTouchEvent(ev: MotionEvent?): Boolean {
        if (ev != null) {
            when (ev.action) {
                MotionEvent.ACTION_DOWN -> super.onTouchEvent(ev)
                MotionEvent.ACTION_MOVE -> return false
                MotionEvent.ACTION_CANCEL -> super.onTouchEvent(ev)
                MotionEvent.ACTION_UP -> return false
            }
        }
        return false
    }

    @SuppressLint("ClickableViewAccessibility")
    override fun onTouchEvent(ev: MotionEvent?): Boolean {
        super.onTouchEvent(ev)
        return true
    }
}