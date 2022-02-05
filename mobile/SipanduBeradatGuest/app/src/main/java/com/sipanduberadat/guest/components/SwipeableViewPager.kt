package com.sipanduberadat.guest.components

import android.content.Context
import android.util.AttributeSet
import android.view.MotionEvent
import android.view.View
import androidx.viewpager.widget.ViewPager
import com.sipanduberadat.guest.R
import kotlin.math.max

class SwipeableViewPager(context: Context, attr: AttributeSet? = null):
    ViewPager(context, attr) {

    private var swipeable = true
    private var autoMeasure = false
    private var measurePerChild = true

    private fun onPageChangeListener() {
        addOnPageChangeListener(object: OnPageChangeListener {
            override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}

            override fun onPageSelected(position: Int) {
                requestLayout()
            }

            override fun onPageScrollStateChanged(state: Int) {}
        })
    }

    init {
        val typedArray = context.obtainStyledAttributes(attr, R.styleable.SwipeableViewPager)
        swipeable = typedArray.getBoolean(R.styleable.SwipeableViewPager_swipeable, swipeable)
        autoMeasure = typedArray.getBoolean(R.styleable.SwipeableViewPager_autoMeasure, autoMeasure)
        measurePerChild = typedArray.getBoolean(R.styleable.SwipeableViewPager_measurePerChild, measurePerChild)
        typedArray.recycle()
        if (autoMeasure) onPageChangeListener()
    }

    override fun onInterceptTouchEvent(ev: MotionEvent?): Boolean {
        return if (swipeable) super.onInterceptTouchEvent(ev) else false
    }

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        if (!autoMeasure) {
            super.onMeasure(widthMeasureSpec, heightMeasureSpec)
            return
        }

        val childWidthSpec = MeasureSpec.makeMeasureSpec(
                max(0, MeasureSpec.getSize(widthMeasureSpec) -
                        paddingLeft - paddingRight),
                MeasureSpec.getMode(widthMeasureSpec)
        )

        if (measurePerChild) {
            if (getChildAt(currentItem) != null) {
                val child: View = getChildAt(currentItem)
                child.measure(childWidthSpec, MeasureSpec.UNSPECIFIED)
                val newHeightMeasureSpec = MeasureSpec.makeMeasureSpec(child.measuredHeight,
                        MeasureSpec.EXACTLY)
                super.onMeasure(widthMeasureSpec, newHeightMeasureSpec)
                return
            }
        } else {
            var maxHeight = 0

            for (i in 0 until childCount) {
                val child = getChildAt(i)

                if (child != null) {
                    child.measure(childWidthSpec, MeasureSpec.UNSPECIFIED)
                    maxHeight = if (child.measuredHeight > maxHeight) child.measuredHeight else maxHeight
                }
            }

            val newHeightMeasureSpec = MeasureSpec.makeMeasureSpec(maxHeight, MeasureSpec.EXACTLY)
            super.onMeasure(widthMeasureSpec, newHeightMeasureSpec)
            return
        }

        super.onMeasure(widthMeasureSpec, heightMeasureSpec)
    }

}